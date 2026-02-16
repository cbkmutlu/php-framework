<?php

declare(strict_types=1);

use App\Core\Abstracts\Migration;

class product extends Migration {
   public function up(): void {
      $this->database->query("CREATE TABLE IF NOT EXISTS `product` (
         `id` INT AUTO_INCREMENT PRIMARY KEY,
         `brand_id` INT NOT NULL DEFAULT 0,
         `code` VARCHAR(50) NOT NULL,
         `title` VARCHAR(100) NOT NULL,
         `content` TEXT NULL DEFAULT NULL,
         `price` DECIMAL(10,2) NOT NULL DEFAULT 0,
         `stock` INT NOT NULL DEFAULT 0,
         `date` TIMESTAMP(3) NULL DEFAULT NULL,
         `is_active` BOOLEAN NOT NULL DEFAULT 1,
         `sort_order` INT NOT NULL DEFAULT 0,
         {$this->defaults()}
      )");

      $this->database->table('product')
         ->insert([
            'brand_id' => [1],
            'code' => ['PRD001'],
            'title' => ['Ürün Başlığı'],
            'content' => ['Ürün Açıklaması'],
            'is_active' => [1],
            'sort_order' => [1]
         ])
         ->prepare()
         ->execute();

      $this->database->table('product')
         ->insert([
            'brand_id' => [1],
            'code' => ['PRD002'],
            'title' => ['Ürün Başlığı'],
            'content' => ['Ürün Açıklaması'],
            'is_active' => [1],
            'sort_order' => [1]
         ])
         ->prepare()
         ->execute();
   }

   public function down(): void {
      $this->database->query("DROP TABLE IF EXISTS `product`");
   }
}
