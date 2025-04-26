<?php

declare(strict_types=1);

use System\Migration\Migration;

class company extends Migration {
   public function up() {
      $this->database->query("CREATE TABLE IF NOT EXISTS company (
         `id` INT AUTO_INCREMENT PRIMARY KEY,
         `code` VARCHAR(55) NOT NULL,
         `title` VARCHAR(55) NOT NULL,
         `tax_office` VARCHAR(55) NULL,
         `tax_number` VARCHAR(55) NULL,
         `phone` VARCHAR(55) NULL,
         `email` VARCHAR(55) NULL,
         `address` VARCHAR(55) NULL,
         `is_deleted` BOOLEAN NOT NULL DEFAULT 0,
         `notes` VARCHAR(255) NULL,
         {$this->defaults()}
      )");
   }

   public function down() {
      $this->database->query("DROP TABLE IF EXISTS company");
   }
}
