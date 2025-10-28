<?php

declare(strict_types=1);

namespace App\Modules\File;

use System\Database\Database;
use App\Core\Abstracts\BaseRepository;

class FileRepository extends BaseRepository {
   public function __construct(
      protected Database $database,
      protected string $table = 'file'
   ) {
   }
}
