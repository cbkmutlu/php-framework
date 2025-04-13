<?php

declare(strict_types=1);

use System\Migration\Migration;

class customers extends Migration {
   public function up() {
      $this->database->query("CREATE TABLE IF NOT EXISTS customers (
         `id` INT AUTO_INCREMENT PRIMARY KEY,
         `name` VARCHAR(50) NOT NULL,
         `surname` VARCHAR(50) NOT NULL,
         `email` VARCHAR(150) UNIQUE NOT NULL,
         `phone` VARCHAR(50) NOT NULL,
         `tckn` VARCHAR(50) NOT NULL,
         `address` VARCHAR(50) NOT NULL,
         `debit` DECIMAL(10,2) NOT NULL DEFAULT 0,
         `notes` VARCHAR(255) NOT NULL,
         `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
         `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
      )");
   }

   public function down() {
      $this->database->query("DROP TABLE IF EXISTS customers");
   }
}
