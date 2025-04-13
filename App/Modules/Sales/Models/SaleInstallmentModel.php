<?php

declare(strict_types=1);

namespace App\Modules\Sales\Models;

use System\Database\Database;
use System\Date\Date;
use System\Model\Model;

class SaleInstallmentModel extends Model {
   public function __construct(
      private Database $database,
      private Date $date
   ) {
   }

   public function add(array $data) {
      $this->database->prepare('INSERT INTO `installments` SET
         `sale_id`=:sale_id,
         `price`=:price,
         `payment`=:payment,
         `due_at`=:due_at
         ');

      $result = $this->database->execute([
         'sale_id' => $data['sale_id'],
         'price' => $data['price'],
         'payment' => $data['payment'],
         'due_at' => $data['due_at']
      ]);

      if ($result) {
         return $this->database->getLastRow('installments');
      }
      return false;
   }

   public function delete(int $id) {
      $this->database->prepare('DELETE FROM `installments` WHERE `id`=:id');
      $result = $this->database->execute([
         'id' => $id
      ]);

      if ($result->getAffectedRows() > 0) {
         return true;
      }

      return false;
   }

   public function update(array $data) {
      $this->database->prepare(
         'UPDATE `installments` SET
      `sale_id`=:sale_id,
      `price`=:price,
      `payment`=:payment,
      `due_at`=:due_at
      WHERE `id`=:id'
      );

      $result = $this->database->execute([
         'sale_id' => $data['sale_id'],
         'price' => $data['price'],
         'payment' => $data['payment'],
         'due_at' => $data['due_at'],
         'id' => $data['id']
      ]);

      if ($result) {
         return true;
      }

      return false;
   }

   public function getInstallmentsBySaleId(int $saleId) {
      $this->database->prepare('SELECT * FROM `installments` WHERE `sale_id`=:sale_id');
      $result = $this->database->execute([
         'sale_id' => $saleId
      ]);

      return $result->getAll();
   }
}
