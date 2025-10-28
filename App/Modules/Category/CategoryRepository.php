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
}
