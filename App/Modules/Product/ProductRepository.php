<?php

declare(strict_types=1);

namespace App\Modules\Product;

use System\Database\Database;
use App\Core\Abstracts\BaseRepository;

class ProductRepository extends BaseRepository {
   public function __construct(
      protected Database $database,
      protected string $table = 'product'
   ) {
   }

   public function findCategory(int $product_id): array {
      return $this->database
         ->prepare('SELECT
               category.id,
               category.title
            FROM product_category

            JOIN category ON category.id = product_category.category_id
               AND category.deleted_at IS NULL
            WHERE product_category.product_id = :product_id
         ')
         ->execute([
            'product_id' => $product_id,
         ])
         ->fetchAll();
   }

   public function findImage(int $product_id): array {
      return $this->database
         ->prepare('SELECT
               product_image.id,
               product_image.product_id,
               product_image.image_path
            FROM product_image
            WHERE product_image.deleted_at IS NULL
               AND product_image.image_path IS NOT NULL
               AND product_image.product_id = :product_id
            ORDER BY product_image.sort_order ASC, product_image.created_at ASC
         ')
         ->execute([
            'product_id' => $product_id,
         ])
         ->fetchAll();
   }
}
