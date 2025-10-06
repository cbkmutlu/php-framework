<?php

declare(strict_types=1);

namespace App\Modules\Category;

use System\Database\Database;
use App\Core\Abstracts\BaseRepository;

class CategoryRepository extends BaseRepository {
   public function __construct(
      protected Database $database,
      protected string $table = 'category'
   ) {
   }

   public function findAll(): array {
      return $this->database
         ->prepare('SELECT
               category.*
            FROM category
            WHERE category.deleted_at IS NULL
         ')
         ->execute()
         ->fetchAll();
   }

   public function findOne(int $id): array|false {
      return $this->database
         ->prepare('SELECT
               category.*
            FROM category
            WHERE category.deleted_at IS NULL
               AND category.id = :id
         ')
         ->execute([
            'id' => $id
         ])
         ->fetch();
   }
}
