<?php

declare(strict_types=1);

namespace App\Modules\Role;

use App\Core\Abstracts\Policy;

class RolePolicy extends Policy {
   /**
    * Listeleme yetkisi
    */
   public function viewAny(?array $user): bool {
      return $this->hasPermission($user, 'role:viewAny');
   }

   /**
    * Görüntüleme yetkisi
    */
   public function view(?array $user, mixed $model = null): bool {
      return $this->hasPermission($user, 'role:view');
   }

   /**
    * Oluşturma yetkisi
    */
   public function create(?array $user): bool {
      return $this->hasPermission($user, 'role:create');
   }

   /**
    * Güncelleme yetkisi
    */
   public function update(?array $user, mixed $model = null): bool {
      return $this->hasPermission($user, 'role:update');
   }

   /**
    * Silme yetkisi
    */
   public function delete(?array $user, mixed $model = null): bool {
      return $this->hasPermission($user, 'role:delete');
   }
}
