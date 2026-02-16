<?php

declare(strict_types=1);

namespace App\Modules\Permission;

use System\Database\Database;
use App\Core\Abstracts\Repository;

class PermissionRepository extends Repository {
   public function __construct(
      protected Database $database,
      protected string $table = 'app_permission'
   ) {
   }
}
