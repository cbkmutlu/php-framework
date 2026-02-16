<?php

declare(strict_types=1);

use App\Core\Abstracts\Migration;

class app_role extends Migration {
   public function up(): void {
      $this->database->query("CREATE TABLE IF NOT EXISTS `app_role` (
         `id` INT AUTO_INCREMENT PRIMARY KEY,
         `name` VARCHAR(100) NOT NULL UNIQUE,
         `slug` VARCHAR(100) NOT NULL UNIQUE,
         `description` TEXT NULL DEFAULT NULL,
         {$this->defaults()}
      )");
   }

   public function down(): void {
      $this->database->query("DROP TABLE IF EXISTS `app_role`");
   }
}
