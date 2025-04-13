<?php

declare(strict_types=1);

use System\Migration\Migration;

class installments extends Migration {
   public function up() {
      $this->database->query("CREATE TABLE IF NOT EXISTS installments (
         `id` INT AUTO_INCREMENT PRIMARY KEY,
         `sale_id` INT NOT NULL,
         `price` DECIMAL(10,2) NOT NULL,
         `payment` DECIMAL(10,2) DEFAULT 0,
         `due_at` DATETIME NOT NULL,
         `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
         `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
         FOREIGN KEY (sale_id) REFERENCES sales(id) ON DELETE CASCADE

      )");
   }

   public function down() {
      $this->database->query("DROP TABLE IF EXISTS installments");
   }
}
