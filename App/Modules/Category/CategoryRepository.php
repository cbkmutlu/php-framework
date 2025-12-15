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
}
