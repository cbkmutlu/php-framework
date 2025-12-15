<?php

declare(strict_types=1);

namespace App\Modules\File;

use System\Database\Database;
use App\Core\Abstracts\Repository;

class FileRepository extends Repository {
   public function __construct(
      protected Database $database,
      protected string $table = 'file'
   ) {
   }
}
