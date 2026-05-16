<?php

declare(strict_types=1);

namespace App\Modules\Auth;

use System\Database\Database;
use App\Core\Abstracts\Repository;

class AuthRepository extends Repository {
    public function __construct(
        protected Database $database,
        protected string $table = 'app_user'
    ) {
    }

    /**
     * Finds a user by email address
     */
    public function findUserByEmail(string $email): ?array {
        return $this->database
            ->prepare('SELECT * FROM app_user WHERE email = :email')
            ->execute([
                'email' => $email
            ])
            ->fetchOne();
    }

    /**
     * Finds a token by hash
     */
    public function findTokenByHash(string $hash): ?array {
        return $this->database
            ->prepare('SELECT * FROM app_user_token WHERE hash_token = :hash AND expires_at > NOW()')
            ->execute([
                'hash' => $hash
            ])
            ->fetchOne();
    }

    /**
     * Finds a token by ID
     */
    public function findTokenById(int $tokenId): ?array {
        return $this->database
            ->prepare('SELECT * FROM app_user_token WHERE id = :id')
            ->execute([
                'id' => $tokenId
            ])
            ->fetchOne();
    }

    /**
     * Finds a token by JTI
     */
    public function findTokenByJti(string $jti): ?array {
        return $this->database
            ->prepare('SELECT * FROM app_user_token WHERE jti = :jti')
            ->execute([
                'jti' => $jti
            ])
            ->fetchOne();
    }
}
