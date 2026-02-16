<?php

declare(strict_types=1);

use App\Core\Abstracts\Migration;

class app_user_role extends Migration {
   public function up(): void {
      $this->database->query("CREATE TABLE IF NOT EXISTS `app_user_role` (
         `user_id` INT NOT NULL,
         `role_id` INT NOT NULL,
         `scope_type` VARCHAR(50) NULL DEFAULT NULL COMMENT 'Kapsam tipi (system, company, location, department)',
         `scope_id` INT NULL DEFAULT NULL COMMENT 'Kapsam ID (company_id, location_id, vb.)',
         UNIQUE KEY `user_role_scope_unique` (`user_id`, `role_id`, `scope_type`, `scope_id`),
         CONSTRAINT `fk_app_user_role_user` FOREIGN KEY (`user_id`) REFERENCES `app_user`(`id`) ON DELETE CASCADE,
         CONSTRAINT `fk_app_user_role_role` FOREIGN KEY (`role_id`) REFERENCES `app_role`(`id`) ON DELETE CASCADE
      )");
   }

   public function down(): void {
      $this->database->query("DROP TABLE IF EXISTS `app_user_role`");
   }
}
