<?php

declare(strict_types=1);

namespace App\Modules\Customer\Models;

use System\Database\Database;
use System\Model\Model;

class CommandModel extends Model {

   public function __construct(
      private Database $database
   ) {
   }

   public function add(array $data): mixed {
      $this->database->prepare('INSERT INTO `customers` SET
         `name`=:name,
         `surname`=:surname,
         `email`=:email,
         `phone`=:phone,
         `tckn`=:tckn,
         `address`=:address,
         `notes`=:notes
      ');

      $result = $this->database->execute([
         'name' => $data['name'],
         'surname' => $data['surname'],
         'email' => $data['email'],
         'phone' => $data['phone'],
         'tckn' => $data['tckn'],
         'address' => $data['address'],
         'notes' => $data['notes']
      ]);

      if ($result) {
         return $this->database->getLastRow('customers');
      } else {
         return false;
      }
   }

   public function delete(int $id) {
      if ($this->getCustomerById((int) $id) === []) {
         return false;
      }

      $this->database->prepare('DELETE FROM `customers` WHERE `id`=:id');
      $result = $this->database->execute([
         'id' => $id
      ]);

      if ($result->getAffectedRows() > 0) {
         return true;
      } else {
         return false;
      }
   }

   public function update(array $data): mixed {
      if ($this->getCustomerById((int) $data['id']) === []) {
         return false;
      }

      $this->database->prepare('UPDATE `customers` SET
         `name`=:name,
         `surname`=:surname,
         `email`=:email,
         `phone`=:phone,
         `tckn`=:tckn,
         `address`=:address,
         `notes`=:notes
      WHERE `id`=:id');

      $result = $this->database->execute([
         'name' => $data['name'],
         'surname' => $data['surname'],
         'email' => $data['email'],
         'phone' => $data['phone'],
         'tckn' => $data['tckn'],
         'address' => $data['address'],
         'notes' => $data['notes'],
         'id' => $data['id']
      ]);

      if ($result) {
         return true;
      } else {
         return false;
      }
   }

   private function getCustomerById(int $id): mixed {
      $this->database->prepare('SELECT * FROM `customers` WHERE `id` = :id');
      $result = $this->database->execute(['id' => $id]);
      return $result->getRow();
   }
}
