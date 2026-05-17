<?php

declare(strict_types=1);

use App\Core\Abstracts\Migration;

class approval_flow extends Migration {
    public function up(): void {
        $this->database->query("CREATE TABLE IF NOT EXISTS `approval_flow` (
         `id` INT AUTO_INCREMENT PRIMARY KEY,
         `entity_type` VARCHAR(100) NOT NULL,
         `entity_id` INT NULL DEFAULT NULL,
         `action` VARCHAR(50) NOT NULL,
         `payload` JSON NOT NULL,
         `current_step` INT NOT NULL DEFAULT 1,
         `total_steps` INT NOT NULL,
         `status` ENUM('pending', 'approved', 'rejected', 'cancelled') NOT NULL DEFAULT 'pending',
         `rejected_reason` TEXT NULL DEFAULT NULL,
         {$this->defaults()},
         INDEX `idx_entity` (`entity_type`, `action`),
         INDEX `idx_status` (`status`),
         INDEX `idx_created_by` (`created_by`)
      )");
    }

    public function down(): void {
        $this->database->query("DROP TABLE IF EXISTS `approval_flow`");
    }
}
