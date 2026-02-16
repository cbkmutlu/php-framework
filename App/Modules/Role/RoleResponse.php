<?php

declare(strict_types=1);

namespace App\Modules\Role;

use App\Core\Abstracts\{AuditTrait, Resource};

class RoleResponse extends Resource {
   use AuditTrait;

   public int $id;
   public string $name;
   public string $slug;
   public ?string $description;
   public ?array $permissions;
}
