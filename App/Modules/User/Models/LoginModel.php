<?php

declare(strict_types=1);

namespace App\Modules\User\Models;

use System\Database\Database;
use System\Exception\ExceptionHandler;
use System\Hash\Hash;
use System\Model\Model;

class LoginModel extends Model {

   public function __construct(
      private Database $database,
      private Hash $hash
   ) {
   }

   public function login(array $data): mixed {
      try {
         if (isset($data['email']) && isset($data['password'])) {
            $this->database->prepare("SELECT * FROM users WHERE `email`=:email");
            $result = $this->database->execute([
               'email' => $data['email']
            ]);

            if ($row = $result->getRow()) {
               if ($this->hash->verify($data['password'], $row->password)) {
                  return $row;
               } else {
                  return false;
               }
            } else {
               return false;
            }
         } else {
            return false;
         }
      } catch (\PDOException $th) {
         throw new ExceptionHandler('Query Error', '[' . $th->getCode() . ']: ' . $th->getMessage());
      }
   }
}
