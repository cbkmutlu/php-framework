<?php

declare(strict_types=1);

namespace App\Modules\Auth;

use System\Crypt\Crypt;
use System\Date\Date;
use System\Exception\SystemException;
use System\Http\Request;
use System\Jwt\Jwt;
use System\Validation\Validation;
use App\Core\Abstracts\Service;
use App\Modules\Auth\AuthRepository;

class AuthService extends Service {
    protected ?int $accessTokenExpire;
    protected ?int $refreshTokenExpire;

    public function __construct(
        protected Crypt $crypt,
        protected Jwt $jwt,
        protected Validation $validation,
        protected Request $request,
        protected Date $date,
        protected AuthRepository $repository
    ) {
        $expire = import_config('defines.jwt.expire');
        $this->accessTokenExpire = $expire['access'];
        $this->refreshTokenExpire = $expire['refresh'];
    }

    /**
     * User login
     */
    public function login(string $email, string $password): array {
        return $this->repository->transaction(function () use ($email, $password): array {
            $user = $this->repository->findUserByEmail($email);

            if ($user === null || !$this->crypt->verify($password, $user['password'])) {
                throw new SystemException('Invalid password or email', 403);
            }

            // generate tokens
            $jti = $this->generateJti();
            $accessToken = $this->generateAccessToken($user['id'], $jti);
            $refreshToken = bin2hex(random_bytes(32));

            // create refresh token
            $this->createRefreshToken($user['id'], $jti, $refreshToken);

            // return tokens
            return [
                'user_id' => $user['id'],
                'user_email' => $user['email'],
                'access_token' => $accessToken,
                'refresh_token' => $refreshToken
            ];
        });
    }

    /**
     * User logout
     */
    public function logout(string $refreshToken): bool {
        // hash refresh token
        $hash = hash('sha256', $refreshToken);
        $token = $this->repository->findTokenByHash($hash);

        // check token
        if ($token === null) {
            return false;
        }

        // revoke refresh token
        $this->revokeRefreshToken($token['id']);

        return true;
    }

    /**
     * User logout all
     */
    public function logoutAll(int $userId): bool {
        // revoke all refresh tokens
        $result = $this->repository->update([
            'revoked_at' => ['NOW(3)']
        ], [
            'user_id' => $userId,
            'revoked_at' => ['IS NULL']
        ], 'app_user_token');

        // return true if any token was revoked
        return $result->affectedRows() > 0;
    }

    /**
     * Refresh token
     */
    public function refresh(string $refreshToken): array {
        return $this->repository->transaction(function () use ($refreshToken): array {
            // hash refresh token
            $hash  = hash('sha256', $refreshToken);
            $token = $this->repository->findTokenByHash($hash);

            // check token
            if ($token === null) {
                throw new SystemException('Token not found', 403);
            }

            // check user agent
            if ($token['user_agent'] !== $this->request->userAgent()) {
                $this->revokeRefreshToken($token['id']);
                throw new SystemException('Token invalid', 403);
            }

            // lock refresh token
            $locked = $this->lockRefreshToken($token['id'], $hash);

            // check if token is locked
            if ($locked === false) {
                $freshToken = $this->repository->findTokenById((int)$token['id']);

                // check if token has next id
                if ($freshToken && $freshToken['next_id']) {
                    $newToken = $this->repository->findTokenById((int) $freshToken['next_id']);

                    // check if token is valid
                    if ($newToken === null) {
                        throw new SystemException('Token chain broken', 403);
                    }

                    $user = $this->repository->findOne($newToken['user_id']);

                    // return tokens
                    return [
                        'user_id'      => $user['id'],
                        'user_email'   => $user['email'],
                        'access_token' => $this->generateAccessToken($user['id'], $newToken['jti']),
                    ];
                }

                throw new SystemException('Refresh in progress', 409);
            }

            try {
                // rotate token
                return $this->rotateToken($token);
            } finally {
                // release refresh token
                $this->releaseRefreshToken($token['id'], $hash);
            }
        });
    }

    /**
     * Generate jti
     */
    private function generateJti(): string {
        // generate jti
        $jti = bin2hex(random_bytes(16));
        $record = $this->repository->findTokenByJti($jti);

        // check if jti is valid
        if ($record !== null) {
            return $this->generateJti();
        }

        // return jti
        return $jti;
    }

    /**
     * Rotate token
     */
    private function rotateToken(array $token): array {
        // get user
        $user = $this->repository->findOne($token['user_id']);

        // generate tokens
        $jti = $this->generateJti();
        $accessToken  = $this->generateAccessToken($user['id'], $jti);
        $refreshToken = bin2hex(random_bytes(32));

        // create refresh token
        $newId = $this->createRefreshToken($user['id'], $jti, $refreshToken);
        $this->chainRefreshToken($token['id'], $newId);

        return [
            'user_id'       => $user['id'],
            'user_email'    => $user['email'],
            'access_token'  => $accessToken,
            'refresh_token' => $refreshToken
        ];
    }

    /**
     * Generate access token
     */
    private function generateAccessToken(int $userId, string $jti): string {
        // generate access token
        $accessToken = $this->jwt->createToken([
            'id' => $userId,
            'jti' => $jti,
            'exp' => time() + $this->accessTokenExpire
        ]);

        // return access token
        return $accessToken;
    }

    /**
     * Create refresh token
     */
    private function createRefreshToken(int $userId, string $jti, string $refreshToken): int {
        // set expire date
        $expire = $this->date->setDate('now')->modifyDate('+' . $this->refreshTokenExpire . ' second');

        // create refresh token
        $result = $this->repository->create([
            'jti' => $jti,
            'user_id' => $userId,
            'user_ip' => $this->request->userIp(),
            'user_agent' => $this->request->userAgent(),
            'hash_token' => hash('sha256', $refreshToken),
            'created_at' => ['NOW(3)'],
            'expires_at' => $expire->getDate(Date::GENERIC3)
        ], 'app_user_token');

        // return refresh token id
        return $result->lastInsertId();
    }

    /**
     * Revoke refresh token
     */
    private function revokeRefreshToken(int $tokenId): void {
        // revoke refresh token
        $this->repository->update([
            'revoked_at' => ['NOW(3)']
        ], [
            'id' => $tokenId
        ], 'app_user_token');

        // revoke next token
        $record = $this->repository->findTokenById($tokenId);
        if (!empty($record['next_id'])) {
            $this->revokeRefreshToken($record['next_id']);
        }
    }

    /**
     * Chain refresh token
     */
    private function chainRefreshToken(int $tokenId, int $nextId): void {
        // chain refresh token
        $this->repository->update([
            'next_id' => $nextId,
            'revoked_at' => ['NOW(3)']
        ], [
            'id' => $tokenId
        ], 'app_user_token');
    }

    /**
     * Lock refresh token
     */
    private function lockRefreshToken(int $tokenId, string $hash): bool {
        // lock refresh token
        $result = $this->repository->update([
            'requested_at' => ['NOW(3)']
        ], [
            'id' => $tokenId,
            'requested_at' => ['IS NULL OR `requested_at` < NOW(3) - INTERVAL 3 SECOND'],
            'revoked_at' => ['IS NULL']
        ], 'app_user_token');

        // check if token is locked
        if ($result->affectedRows() !== 1) {
            return false;
        }

        // lock token
        if (function_exists('apcu_add')) {
            apcu_add('refresh_lock:' . $hash, 1, 3);
        }

        return true;
    }

    /**
     * Release refresh token
     */
    private function releaseRefreshToken(int $tokenId, string $hash): void {
        // release refresh token
        $this->repository->update([
            'requested_at' => null
        ], [
            'id' => $tokenId,
            'revoked_at' => ['IS NULL']
        ], 'app_user_token');

        // delete token lock
        if (function_exists('apcu_delete')) {
            apcu_delete('refresh_lock:' . $hash);
        }
    }
}
