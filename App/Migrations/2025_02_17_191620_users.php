<?php

declare(strict_types=1);

use System\Migration\Migration;

class users extends Migration {
   public function up() {
      $this->database->query("CREATE TABLE IF NOT EXISTS users (
         `id` INT AUTO_INCREMENT PRIMARY KEY,
         `name` VARCHAR(55) NOT NULL,
         `surname` VARCHAR(55) NOT NULL,
         `email` VARCHAR(155) NOT NULL,
         `password` VARCHAR(255) NOT NULL,
         `phone` VARCHAR(55) NOT NULL,
         `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
         `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
      )");
   }

   public function down() {
      $this->database->query("DROP TABLE IF EXISTS users");
   }
}
