<?php

declare(strict_types=1);

namespace App\Modules\User;

use App\Core\Abstracts\Resource;

class UserRequest extends Resource {
   public ?int $id;
   public string $name;
   public string $surname;
   public ?string $email;
   public ?string $password;
   public int $status;

   public array $roles;
   public array $permissions;

   public function rules(): array {
      return [
         'name'     => ['required', 'max:55'],
         'surname'  => ['required', 'max:55'],
         'email'    => ['required', 'email', 'max:155'],
         'password' => ['nullable', 'min:6'],
         'status'   => ['required', 'numeric']
      ];
   }

   public function labels(): array {
      return [
         'name'     => 'Ad',
         'surname'  => 'Soyad',
         'email'    => 'E-posta',
         'password' => 'Şifre',
         'status'   => 'Durum'
      ];
   }
}
