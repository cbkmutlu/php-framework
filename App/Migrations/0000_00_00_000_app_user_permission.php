<?php

declare(strict_types=1);

use App\Core\Abstracts\Migration;

class app_user_permission extends Migration {
   public function up(): void {
      $this->database->query("CREATE TABLE IF NOT EXISTS `app_user_permission` (
         `user_id` INT NOT NULL,
         `permission_id` INT NOT NULL,
         `type` ENUM('allow', 'deny') NOT NULL DEFAULT 'allow',
         `scope_type` VARCHAR(50) NULL DEFAULT NULL COMMENT 'Kapsam tipi (system, company, location, department)',
         `scope_id` INT NULL DEFAULT NULL COMMENT 'Kapsam ID (company_id, location_id, vb.)',
         UNIQUE KEY `unique_user_permission_scope` (`user_id`, `permission_id`, `scope_type`, `scope_id`),
         CONSTRAINT `fk_app_user_permission_user` FOREIGN KEY (`user_id`) REFERENCES `app_user`(`id`) ON DELETE CASCADE,
         CONSTRAINT `fk_app_user_permission_permission` FOREIGN KEY (`permission_id`) REFERENCES `app_permission`(`id`) ON DELETE CASCADE
      )");
   }

   public function down(): void {
      $this->database->query("DROP TABLE IF EXISTS `app_user_permission`");
   }
}
