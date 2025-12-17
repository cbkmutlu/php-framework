<?php

declare(strict_types=1);

namespace App\Modules\Product;

use System\Database\Database;
use App\Core\Abstracts\Repository;

class ProductRepository extends Repository {
   public function __construct(
      protected Database $database,
      protected string $table = 'product'
   ) {
   }

   public function findAllCategory(int $productId): array {
      return $this->database
         ->prepare("SELECT c.*, tr.* FROM product_category pc
            JOIN category c ON c.id = pc.category_id
               AND c.deleted_at IS NULL
            LEFT JOIN category_translate tr ON tr.category_id = c.id
               AND tr.language_id = 1
            WHERE pc.product_id = :product_id
         ")
         ->execute([
            'product_id' => $productId,
         ])
         ->fetchAll();
   }

   public function findAllImage(int $productId): array {
      return $this->database
         ->prepare("SELECT pi.* FROM product_image pi
            WHERE pi.image_path IS NOT NULL
               AND pi.product_id = :product_id
            ORDER BY pi.sort_order ASC
         ")
         ->execute([
            'product_id' => $productId,
         ])
         ->fetchAll();
   }

   public function findOneImage(int $imageId): array|false {
      return $this->database
         ->prepare("SELECT pi.* FROM product_image pi
            WHERE pi.image_path IS NOT NULL
               AND pi.id = :image_id
         ")
         ->execute([
            'image_id' => $imageId,
         ])
         ->fetch();
   }

   public function findOneBrand(int $brandId): array|false {
      return $this->database
         ->prepare("SELECT b.* FROM brand b
            WHERE b.id = :brand_id
         ")
         ->execute([
            'brand_id' => $brandId,
         ])
         ->fetch();
   }
}
