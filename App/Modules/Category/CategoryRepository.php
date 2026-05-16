<?php

declare(strict_types=1);

namespace App\Modules\Category;

use System\Database\Database;
use App\Core\Abstracts\Repository;

class CategoryRepository extends Repository {
    public function __construct(
        protected Database $database,
        protected string $table = 'category'
    ) {
    }

    /**
     * Find all with translate
     */
    public function findAllWithTranslate(int $langId = 1): array {
        return $this->database
            ->prepare("SELECT
               c.*,
               tr.*
            FROM {$this->table} AS c
            LEFT JOIN category_translate AS tr ON tr.category_id = c.id
               AND tr.language_id = :lang_id
            WHERE c.deleted_at IS NULL
         ")
            ->execute([
                'lang_id' => $langId
            ])
            ->fetchAll();
    }

    /**
     * Find one with translate
     */
    public function findOneWithTranslate(int $categoryId, int $langId = 1): ?array {
        return $this->database
            ->prepare("SELECT
               c.*,
               tr.*,
               COALESCE(tr.title, df.title) AS `title`,
               COALESCE(tr.content, df.content) AS `content`
            FROM {$this->table} AS c
            LEFT JOIN category_translate AS tr ON tr.category_id = c.id
               AND tr.language_id = :lang_id
            LEFT JOIN category_translate AS df ON df.category_id = c.id
               AND df.language_id = 1
            WHERE c.id = :id
               AND c.deleted_at IS NULL
         ")
            ->execute([
                'id' => $categoryId,
                'lang_id' => $langId
            ])
            ->fetchOne();
    }

    /**
     * Find updated
     */
    public function findUpdated(string $columns): array {
        return $this->database
            ->prepare("SELECT id, sort_order FROM {$this->table} WHERE id IN ({$columns}) AND deleted_at IS NULL ORDER BY sort_order ASC")
            ->execute()
            ->fetchAll();
    }

    /**
     * Find image_path
     */
    public function findImagePath(int $categoryId): ?string {
        return $this->database
            ->prepare("SELECT image_path FROM {$this->table} WHERE id = :category_id LIMIT 1")
            ->execute([
                'category_id' => $categoryId
            ])
            ->fetchColumn();
    }

    /**
     * Check product relation
     */
    public function hasProductRelation(int $categoryId): bool {
        return $this->database
            ->prepare("SELECT product_id FROM product_category WHERE category_id = :category_id LIMIT 1")
            ->execute([
                'category_id' => $categoryId
            ])
            ->fetchColumn() !== null;
    }
}
