<?php

declare(strict_types=1);

namespace App\Modules\Role;

use App\Core\Abstracts\Resource;

class RoleRequest extends Resource {
   public ?int $id;
   public string $name;
   public string $slug;
   public ?string $description;

   public array $permissions;

   public function rules(): array {
      return [
         'name'        => ['required', 'max:100'],
         'slug'        => ['required', 'max:100'],
         'description' => ['nullable']
      ];
   }

   public function labels(): array {
      return [
         'name'        => 'Rol adı',
         'slug'        => 'Rol slug',
         'description' => 'Açıklama'
      ];
   }
}
