<?php

declare(strict_types=1);

namespace App\Modules\Sales\Models;

use System\Database\Database;
use System\Model\Model;

class SaleCommandModel extends Model {
   public function __construct(
      private Database $database
   ) {
   }

   public function add(array $data) {
      $this->database->prepare('INSERT INTO `sales` SET
      `customer_id`=:customer_id,
      `product`=:product,
      `price`=:price,
      `installment`=:installment,
      `installment_type`=:installment_type
      ');

      $result = $this->database->execute([
         'customer_id' => $data['customer_id'],
         'product' => $data['product'],
         'price' => $data['price'],
         'installment' => $data['installment'],
         'installment_type' => $data['installment_type']
      ]);

      if ($result) {
         return $this->database->getLastRow('sales');
      } else {
         return false;
      }
   }

   public function delete(int $id) {
      if ($this->getSaleById((int) $id) === []) {
         return false;
      }

      $this->database->prepare('DELETE FROM `sales` WHERE `id`=:id');
      $result = $this->database->execute([
         'id' => $id
      ]);

      if ($result->getAffectedRows() > 0) {
         return true;
      } else {
         return false;
      }
   }

   public function update(array $data) {
      if ($this->getSaleById((int) $data['id']) === []) {
         return false;
      }

      $this->database->prepare(
         'UPDATE `sales` SET
      `customer_id`=:customer_id,
      `product`=:product,
      `price`=:price,
      `installment`=:installment,
      `installment_type`=:installment_type
      WHERE `id`=:id'
      );

      $result = $this->database->execute([
         'customer_id' => $data['customer_id'],
         'product' => $data['product'],
         'price' => $data['price'],
         'installment' => $data['installment'],
         'installment_type' => $data['installment_type'],
         'id' => $data['id']
      ]);

      if ($result) {
         return true;
      } else {
         return false;
      }
   }

   public function getSaleById(int $id) {
      $this->database->prepare('SELECT * FROM `sales` WHERE `id` = :id');
      $result = $this->database->execute(['id' => $id]);
      return $result->getRow();
   }
}
