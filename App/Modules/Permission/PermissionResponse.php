<?php

declare(strict_types=1);

namespace App\Modules\Permission;

use App\Core\Abstracts\{AuditTrait, Resource};

class PermissionResponse extends Resource {
   use AuditTrait;

   public int $id;
   public string $name;
   public string $slug;
   public ?string $group_name;
   public ?string $description;
}
