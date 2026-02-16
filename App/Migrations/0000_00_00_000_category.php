<?php

declare(strict_types=1);

use App\Core\Abstracts\Migration;

class category extends Migration {
   public function up(): void {
      $this->database->query("CREATE TABLE IF NOT EXISTS `category` (
         `id` INT AUTO_INCREMENT PRIMARY KEY,
         `code` VARCHAR(50) NOT NULL,
         `image_path` VARCHAR(512) NULL DEFAULT NULL,
         `is_active` BOOLEAN NOT NULL DEFAULT 1,
         `sort_order` INT NOT NULL DEFAULT 0,
         {$this->defaults()}
      )");

      $this->database->table('category')
         ->insert([
            'code' => ['CAT001'],
            'is_active' => [1],
            'sort_order' => [1]
         ])
         ->prepare()
         ->execute();
   }

   public function down(): void {
      $this->database->query("DROP TABLE IF EXISTS `category`");
   }
}
