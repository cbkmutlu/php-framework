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

    /**
     * Find all category by product id
     */
    public function findAllCategory(int $productId): array {
        return $this->database
            ->prepare("SELECT
               c.*,
               tr.*
            FROM product_category pc
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

    /**
     * Find all image by product id
     */
    public function findAllImage(int $productId): array {
        return $this->database
            ->prepare("SELECT * FROM product_image WHERE image_path IS NOT NULL AND product_id = :product_id ORDER BY sort_order ASC")
            ->execute([
                'product_id' => $productId,
            ])
            ->fetchAll();
    }

    /**
     * Find brand by brand id
     */
    public function findBrand(int $brandId): ?array {
        return $this->database
            ->prepare("SELECT * FROM brand WHERE id = :brand_id")
            ->execute([
                'brand_id' => $brandId
            ])
            ->fetchOne();
    }

    /**
     * Find image path by image id
     */
    public function findImagePath(int $imageId): ?string {
        return $this->database
            ->prepare("SELECT image_path FROM product_image WHERE id = :image_id AND image_path IS NOT NULL")
            ->execute([
                'image_id' => $imageId
            ])
            ->fetchColumn();
    }
}
