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

   public function findAll(int $lang_id = 1): array {
      return $this->database
         ->prepare("SELECT c.*, tr.* FROM {$this->table} c
            LEFT JOIN category_translate tr ON tr.category_id = c.id
               AND tr.language_id = :lang_id
            WHERE c.deleted_at IS NULL
         ")
         ->execute([
            'lang_id' => $lang_id
         ])
         ->fetchAll();
   }

   public function findOne(int $id, int $lang_id = 1): array|false {
      return $this->database
         ->prepare("SELECT
               c.*, tr.*,
               COALESCE(tr.title, df.title) AS `title`,
               COALESCE(tr.content, df.content) AS `content`
            FROM {$this->table} c
            LEFT JOIN category_translate tr ON tr.category_id = c.id
               AND tr.language_id = :lang_id
            LEFT JOIN category_translate AS df ON df.category_id = c.id
               AND df.language_id = 1
            WHERE c.id = :id
               AND c.deleted_at IS NULL
         ")
         ->execute([
            'id' => $id,
            'lang_id' => $lang_id
         ])
         ->fetch();
   }
}
