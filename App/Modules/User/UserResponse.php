<?php

declare(strict_types=1);

namespace App\Modules\User;

use App\Core\Abstracts\{AuditTrait, Resource};

class UserResponse extends Resource {
   use AuditTrait;

   public int $id;
   public string $name;
   public string $surname;
   public ?string $email;
   public int $status;
   public ?array $roles;
   public ?array $permissions;
}
