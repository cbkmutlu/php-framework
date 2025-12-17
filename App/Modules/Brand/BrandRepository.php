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
}
