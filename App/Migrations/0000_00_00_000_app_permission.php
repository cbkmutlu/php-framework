<?php

declare(strict_types=1);

use App\Core\Abstracts\Migration;

class app_permission extends Migration {
   public function up(): void {
      $this->database->query("CREATE TABLE IF NOT EXISTS `app_permission` (
         `id` INT AUTO_INCREMENT PRIMARY KEY,
         `name` VARCHAR(100) NOT NULL UNIQUE,
         `slug` VARCHAR(100) NOT NULL UNIQUE,
         `group_name` VARCHAR(100) NULL DEFAULT NULL,
         `description` TEXT NULL DEFAULT NULL,
         {$this->defaults()}
      )");
   }

   public function down(): void {
      $this->database->query("SET FOREIGN_KEY_CHECKS=0");
      $this->database->query("DROP TABLE IF EXISTS `app_permission`");
      $this->database->query("SET FOREIGN_KEY_CHECKS=1");
   }
}
