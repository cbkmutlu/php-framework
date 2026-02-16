<?php

declare(strict_types=1);

use App\Core\Abstracts\Migration;

class app_role_permission extends Migration {
   public function up(): void {
      $this->database->query("CREATE TABLE IF NOT EXISTS `app_role_permission` (
         `role_id` INT NOT NULL,
         `permission_id` INT NOT NULL,
         UNIQUE KEY `role_permission_unique` (`role_id`, `permission_id`),
         CONSTRAINT `fk_app_role_permission_role` FOREIGN KEY (`role_id`) REFERENCES `app_role`(`id`) ON DELETE CASCADE,
         CONSTRAINT `fk_app_role_permission_permission` FOREIGN KEY (`permission_id`) REFERENCES `app_permission`(`id`) ON DELETE CASCADE
      )");
   }

   public function down(): void {
      $this->database->query("DROP TABLE IF EXISTS `app_role_permission`");
   }
}
