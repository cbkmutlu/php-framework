<?php

declare(strict_types=1);

use System\Migration\Migration;

class product extends Migration {
   public function up() {
      $this->database->query("CREATE TABLE IF NOT EXISTS `product` (
         `id` INT AUTO_INCREMENT PRIMARY KEY,
         `code` VARCHAR(50) NOT NULL,
         `title` VARCHAR(100) NOT NULL,
         `content` TEXT NULL DEFAULT NULL,
         `is_active` BOOLEAN NOT NULL DEFAULT 1,
         `sort_order` INT NOT NULL DEFAULT 0,
         {$this->defaults()}
      )");

      $this->database->table('product')->insert([
         'code' => 'PRD001',
         'title' => 'Ürün Başlığı',
         'content' => 'Ürün Açıklaması',
         'is_active' => 1,
         'sort_order' => 1
      ])->prepare()->execute();

      $this->database->table('product')->insert([
         'code' => 'PRD002',
         'title' => 'Ürün Başlığı',
         'content' => 'Ürün Açıklaması',
         'is_active' => 1,
         'sort_order' => 1
      ])->prepare()->execute();
   }

   public function down() {
      $this->database->query("DROP TABLE IF EXISTS `product`");
   }
}
