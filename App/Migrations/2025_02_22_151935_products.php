<?php declare(strict_types=1);

use System\Migration\Migration;

class products extends Migration {
   public function up() {
      $this->database->query("CREATE TABLE IF NOT EXISTS products (
         `id` INT AUTO_INCREMENT PRIMARY KEY,
         `name` VARCHAR(255) NOT NULL,
         `price` DECIMAL(10,2) NOT NULL,
         `description` TEXT NOT NULL,
         `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
      )");
   }

   public function down() {
      $this->database->query("DROP TABLE IF EXISTS products");
   }
}
