<?php

declare(strict_types=1);

namespace App\Modules\Auth;

use System\Jwt\Jwt;
use System\Date\Date;
use System\Crypt\Crypt;
use System\Http\Request;
use App\Core\Abstracts\Service;
use System\Validation\Validation;
use App\Modules\Auth\AuthRepository;
use System\Exception\SystemException;

class AuthService extends Service {
   /** @var AuthRepository */
   protected mixed $repository;

   protected ?int $accessTokenExpire;
   protected ?int $refreshTokenExpire;

   public function __construct(
      protected Crypt $crypt,
      protected Jwt $jwt,
      protected Validation $validation,
      protected Request $request,
      protected Date $date,
      AuthRepository $repository
   ) {
      $this->repository = $repository;
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

         if (empty($user) || !$this->crypt->verify($password, $user['password'])) {
            throw new SystemException('Invalid password or email', 403);
         }

         $jti = $this->generateJti();
         $accessToken = $this->generateAccessToken($user['id'], $jti);
         $refreshToken = bin2hex(random_bytes(32));

         $this->createRefreshToken($user['id'], $jti, $refreshToken);

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
      $hash = hash('sha256', $refreshToken);
      $token = $this->repository->findTokenByHash($hash);

      if (!$token) {
         return false;
      }

      $this->revokeRefreshToken($token['id']);

      return true;
   }

   /**
    * User logout all
    */
   public function logoutAll(int $userId): bool {
      $result = $this->repository->update([
         'revoked_at' => ['NOW(3)']
      ], [
         'user_id' => $userId,
         'revoked_at' => ['IS NULL']
      ], 'api_user_token');

      return $result->affectedRows() > 0;
   }

   /**
    * Refresh token
    */
   public function refresh(string $refreshToken): array {
      return $this->repository->transaction(function () use ($refreshToken): array {
         $hash  = hash('sha256', $refreshToken);
         $token = $this->repository->findTokenByHash($hash);

         if (!$token) {
            throw new SystemException('Token not found', 403);
         }

         if ($token['user_agent'] !== $this->request->userAgent()) {
            $this->revokeRefreshToken($token['id']);
            throw new SystemException('Token invalid', 403);
         }

         $locked = $this->lockRefreshToken($token['id'], $hash);

         if (!$locked) {
            $freshToken = $this->repository->findTokenById((int)$token['id']);

            if ($freshToken && $freshToken['next_id']) {
               $newToken = $this->repository->findTokenById((int) $freshToken['next_id']);

               if (!$newToken) {
                  throw new SystemException('Token chain broken', 403);
               }

               $user = $this->repository->findOne($newToken['user_id']);

               return [
                  'user_id'      => $user['id'],
                  'user_email'   => $user['email'],
                  'access_token' => $this->generateAccessToken($user['id'], $newToken['jti']),
               ];
            }

            throw new SystemException('Refresh in progress', 409);
         }

         try {
            return $this->rotateToken($token);
         } finally {
            $this->releaseRefreshToken($token['id'], $hash);
         }
      });
   }

   /**
    * Generate jti
    */
   private function generateJti(): string {
      $jti = bin2hex(random_bytes(16));
      $record = $this->repository->findTokenByJti($jti);

      if (!empty($record)) {
         return $this->generateJti();
      }

      return $jti;
   }

   /**
    * Rotate token
    */
   private function rotateToken(array $token): array {
      $user = $this->repository->findOne($token['user_id']);

      $jti = $this->generateJti();
      $accessToken  = $this->generateAccessToken($user['id'], $jti);
      $refreshToken = bin2hex(random_bytes(32));

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
      $accessToken = $this->jwt->createToken([
         'id' => $userId,
         'jti' => $jti,
         'exp' => time() + $this->accessTokenExpire
      ]);
      return $accessToken;
   }

   /**
    * Create refresh token
    */
   private function createRefreshToken(int $userId, string $jti, string $refreshToken): int {
      $expire = $this->date->setDate('now')->modifyDate('+' . $this->refreshTokenExpire . ' second');
      $result = $this->repository->create([
         'jti' => $jti,
         'user_id' => $userId,
         'user_ip' => $this->request->userIp(),
         'user_agent' => $this->request->userAgent(),
         'hash_token' => hash('sha256', $refreshToken),
         'created_at' => ['NOW(3)'],
         'expires_at' => $expire->getDate(Date::GENERIC3)
      ], 'api_user_token');

      return $result->lastInsertId();
   }

   /**
    * Revoke refresh token
    */
   private function revokeRefreshToken(int $tokenId): void {
      $this->repository->update([
         'revoked_at' => ['NOW(3)']
      ], [
         'id' => $tokenId
      ], 'api_user_token');

      $record = $this->repository->findTokenById($tokenId);

      if (!empty($record['next_id'])) {
         $this->revokeRefreshToken($record['next_id']);
      }
   }

   /**
    * Chain refresh token
    */
   private function chainRefreshToken(int $id, int $nextId): void {
      $this->repository->update([
         'next_id' => $nextId,
         'revoked_at' => ['NOW(3)']
      ], [
         'id' => $id
      ], 'api_user_token');
   }

   /**
    * Lock refresh token
    */
   private function lockRefreshToken(int $tokenId, string $hash): bool {
      $result = $this->repository->update([
         'requested_at' => ['NOW(3)']
      ], [
         'id' => $tokenId,
         'requested_at' => ['IS NULL OR `requested_at` < NOW(3) - INTERVAL 3 SECOND'],
         'revoked_at' => ['IS NULL']
      ], 'api_user_token');

      if ($result->affectedRows() !== 1) {
         return false;
      }

      if (function_exists('apcu_add')) {
         apcu_add('refresh_lock:' . $hash, 1, 3);
      }

      return true;
   }

   /**
    * Release refresh token
    */
   private function releaseRefreshToken(int $tokenId, string $hash): void {
      $this->repository->update([
         'requested_at' => null
      ], [
         'id' => $tokenId,
         'revoked_at' => ['IS NULL']
      ], 'api_user_token');

      if (function_exists('apcu_delete')) {
         apcu_delete('refresh_lock:' . $hash);
      }
   }
}
