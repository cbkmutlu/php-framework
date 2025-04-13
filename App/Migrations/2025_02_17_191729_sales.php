<?php

declare(strict_types=1);

use System\Migration\Migration;

class sales extends Migration {
   public function up() {
      $this->database->query("CREATE TABLE IF NOT EXISTS sales (
         `id` INT AUTO_INCREMENT PRIMARY KEY,
         `customer_id` INT NOT NULL,
         `product` VARCHAR(255) NOT NULL,
         `price` DECIMAL(10,2) NOT NULL,
         `installment` TINYINT NOT NULL,
         `installment_type` VARCHAR(10) NOT NULL DEFAULT 'monthly',
         `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
         `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
         FOREIGN KEY (customer_id) REFERENCES customers(id)
      )");
   }

   public function down() {
      $this->database->query("DROP TABLE IF EXISTS sales");
   }
}
