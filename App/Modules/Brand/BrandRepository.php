<?php

declare(strict_types=1);

namespace App\Modules\Brand;

use System\Database\Database;
use App\Core\Abstracts\Repository;

class BrandRepository extends Repository {
    public function __construct(
        protected Database $database,
        protected string $table = 'brand'
    ) {
    }

    /**
     * Check product relation by brand id
     */
    public function hasProductRelation(int $brandId): bool {
        return $this->database
            ->prepare("SELECT brand_id FROM product WHERE brand_id = :brand_id AND deleted_at IS NULL LIMIT 1")
            ->execute([
                'brand_id' => $brandId
            ])
            ->fetchColumn() !== null;
    }
}
