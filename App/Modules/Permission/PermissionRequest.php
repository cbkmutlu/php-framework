<?php

declare(strict_types=1);

namespace App\Modules\Permission;

use App\Core\Abstracts\Resource;

class PermissionRequest extends Resource {
   public ?int $id;
   public string $name;
   public string $slug;
   public ?string $group_name;
   public ?string $description;

   public function rules(): array {
      return [
         'name'        => ['required', 'max:100'],
         'slug'        => ['required', 'max:100'],
         'group_name'  => ['nullable', 'max:100'],
         'description' => ['nullable']
      ];
   }

   public function labels(): array {
      return [
         'name'        => 'Yetki adı',
         'slug'        => 'Yetki slug',
         'group_name'  => 'Grup adı',
         'description' => 'Açıklama'
      ];
   }
}
