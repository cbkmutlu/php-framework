<?php

declare(strict_types=1);

use App\Core\Abstracts\Migration;

class brand extends Migration {
   public function up() {
      $this->database->query("CREATE TABLE IF NOT EXISTS `brand` (
         `id` INT AUTO_INCREMENT PRIMARY KEY,
         `title` VARCHAR(100) NOT NULL,
         `content` TEXT NULL DEFAULT NULL,
         `is_active` BOOLEAN NOT NULL DEFAULT 1,
         `sort_order` INT NOT NULL DEFAULT 0,
         {$this->defaults()}
      )");

      $this->database->table('brand')
         ->insert([
            'title' => ['Marka Başlığı'],
            'content' => ['Marka Açıklaması'],
            'is_active' => [1],
            'sort_order' => [1]
         ])
         ->prepare()
         ->execute();
   }

   public function down() {
      $this->database->query("DROP TABLE IF EXISTS `brand`");
   }
}
