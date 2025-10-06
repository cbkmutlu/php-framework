<?php

declare(strict_types=1);

use System\Migration\Migration;

class product_category extends Migration {
   public function up() {
      $this->database->query("CREATE TABLE IF NOT EXISTS `product_category` (
         `category_id` INT NOT NULL DEFAULT 0,
         `product_id` INT NOT NULL DEFAULT 0,
          PRIMARY KEY (`category_id`, `product_id`)
      )");
      //  FOREIGN KEY (`category_id`) REFERENCES `category`(`id`),
      //  FOREIGN KEY (`product_id`) REFERENCES `product`(`id`)

      $this->database->table('product_category')->insert([
         'category_id' => 1,
         'product_id' => 1
      ])->prepare()->execute();

      $this->database->table('product_category')->insert([
         'category_id' => 1,
         'product_id' => 2
      ])->prepare()->execute();
   }

   public function down() {
      $this->database->query("DROP TABLE IF EXISTS `product_category`");
   }
}
