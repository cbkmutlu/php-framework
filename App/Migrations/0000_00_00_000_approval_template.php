<?php

declare(strict_types=1);

use App\Core\Abstracts\Migration;

class approval_template extends Migration {
    public function up(): void {
        $this->database->query("CREATE TABLE IF NOT EXISTS `approval_template` (
         `id` INT AUTO_INCREMENT PRIMARY KEY,
         `entity_type` VARCHAR(100) NOT NULL,
         `action` VARCHAR(50) NOT NULL,
         `step_order` INT NOT NULL,
         `assignee_type` ENUM('user', 'role') NOT NULL,
         `assignee_id` INT NOT NULL,
         {$this->defaults()}
      )");
    }

    public function down(): void {
        $this->database->query("DROP TABLE IF EXISTS `approval_template`");
    }
}
