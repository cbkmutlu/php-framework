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

   public function findUserByEmail(string $email): array|false {
      return $this->database
         ->prepare('SELECT * FROM app_user WHERE email = :email')
         ->execute([
            'email' => $email
         ])
         ->fetch();
   }

   public function findTokenByHash(string $hash): array|false {
      return $this->database
         ->prepare('SELECT * FROM app_user_token WHERE hash_token = :hash AND expires_at > NOW()')
         ->execute([
            'hash' => $hash
         ])
         ->fetch();
   }

   public function findTokenById(int $id): array|false {
      return $this->database
         ->prepare('SELECT * FROM app_user_token WHERE id = :id')
         ->execute([
            'id' => $id
         ])
         ->fetch();
   }

   public function findTokenByJti(string $jti): array|false {
      return $this->database
         ->prepare('SELECT * FROM app_user_token WHERE jti = :jti')
         ->execute([
            'jti' => $jti
         ])
         ->fetch();
   }
}
