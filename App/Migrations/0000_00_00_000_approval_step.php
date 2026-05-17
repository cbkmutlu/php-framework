<?php

declare(strict_types=1);

use App\Core\Abstracts\Migration;

class approval_step extends Migration {
    public function up(): void {
        $this->database->query("CREATE TABLE IF NOT EXISTS `approval_step` (
         `id` INT AUTO_INCREMENT PRIMARY KEY,
         `flow_id` INT NOT NULL,
         `step_order` INT NOT NULL,
         `assignee_type` ENUM('user', 'role') NOT NULL,
         `assignee_id` INT NOT NULL,
         `status` ENUM('pending', 'approved', 'rejected', 'skipped') NOT NULL DEFAULT 'pending',
         `comment` TEXT NULL DEFAULT NULL,
         `decided_by` INT NULL DEFAULT NULL,
         `decided_at` TIMESTAMP NULL DEFAULT NULL,
         {$this->defaults()},
         INDEX `idx_flow_id` (`flow_id`),
         INDEX `idx_status` (`status`),
         FOREIGN KEY (`flow_id`) REFERENCES `approval_flow`(`id`) ON DELETE CASCADE
      )");
    }

    public function down(): void {
        $this->database->query("DROP TABLE IF EXISTS `approval_step`");
    }
}
