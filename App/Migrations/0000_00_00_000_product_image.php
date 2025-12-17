<?php

declare(strict_types=1);

use App\Core\Abstracts\Migration;

class product_image extends Migration {
   public function up() {
      $this->database->query("CREATE TABLE IF NOT EXISTS `product_image` (
         `id` INT AUTO_INCREMENT PRIMARY KEY,
         `product_id` INT NOT NULL DEFAULT 0,
         `image_path` VARCHAR(512) NULL DEFAULT NULL,
         `sort_order` INT NOT NULL DEFAULT 0
      )");
   }

   public function down() {
      $this->database->query("DROP TABLE IF EXISTS `product_image`");
   }
}
