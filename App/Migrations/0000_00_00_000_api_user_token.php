<?php

declare(strict_types=1);

use App\Core\Abstracts\Migration;

class api_user_token extends Migration {
   public function up() {
      $this->database->query("CREATE TABLE IF NOT EXISTS `api_user_token` (
         `id` INT AUTO_INCREMENT PRIMARY KEY,
         `jti` VARCHAR(255) NOT NULL,
         `next_id` INT NULL DEFAULT NULL,
         `user_id` INT NOT NULL,
         `user_ip` VARCHAR(45) NULL DEFAULT NULL,
         `user_agent` VARCHAR(255) NOT NULL,
         `hash_token` VARCHAR(255) NOT NULL,
         `expires_at` TIMESTAMP(3) NOT NULL,
         `requested_at` TIMESTAMP(3) NULL,
         `revoked_at` TIMESTAMP(3) NULL,
         `created_at` TIMESTAMP(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3)
      )");
   }

   public function down() {
      $this->database->query("DROP TABLE IF EXISTS `api_user_token`");
   }
}
