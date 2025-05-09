<?php

declare(strict_types=1);

use System\Migration\Migration;

class user extends Migration {
   public function up() {
      $this->database->query("CREATE TABLE IF NOT EXISTS user (
         `id` INT AUTO_INCREMENT PRIMARY KEY,
         `name` VARCHAR(155) NOT NULL,
         `email` VARCHAR(155) NOT NULL UNIQUE,
         `password` VARCHAR(255) NOT NULL,
         `is_active` BOOLEAN NOT NULL DEFAULT 1,
         {$this->defaults()}
      )");
   }

   public function down() {
      $this->database->query("DROP TABLE IF EXISTS user");
   }
}
