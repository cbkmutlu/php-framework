<?php

declare(strict_types=1);

use App\Core\Abstracts\Migration;

class category_translate extends Migration {
   public function up(): void {
      $this->database->query("CREATE TABLE IF NOT EXISTS `category_translate` (
         `category_id` INT NOT NULL DEFAULT 0,
         `language_id` INT NOT NULL DEFAULT 0,
         `title` VARCHAR(100) NOT NULL,
         `content` TEXT NULL DEFAULT NULL,
         `url` VARCHAR(255) NULL DEFAULT NULL,
         `meta_title` VARCHAR(100) NULL DEFAULT NULL,
         `meta_description` TEXT NULL DEFAULT NULL,
         `meta_keywords` VARCHAR(255) NULL DEFAULT NULL,
         PRIMARY KEY (`category_id`, `language_id`)
      )");

      $this->database->table('category_translate')
         ->insert([
            'category_id' => [1],
            'language_id' => [1],
            'title' => ['Kategori Başlığı'],
            'content' => ['Kategori Açıklaması'],
            'url' => ['/kategori-basligi'],
            'meta_title' => ['Kategori Meta Başlığı'],
            'meta_description' => ['Kategori Meta Açıklaması'],
            'meta_keywords' => ['Kategori Meta Anahtar Kelimeleri']
         ])
         ->prepare()
         ->execute();

      $this->database->table('category_translate')
         ->insert([
            'category_id' => [1],
            'language_id' => [2],
            'title' => ['Category Title'],
            'content' => ['Category Description'],
            'url' => ['/category-title'],
            'meta_title' => ['Category Meta Title'],
            'meta_description' => ['Category Meta Description'],
            'meta_keywords' => ['Category Meta Keywords']
         ])
         ->prepare()
         ->execute();
   }

   public function down(): void {
      $this->database->query("DROP TABLE IF EXISTS `category_translate`");
   }
}
