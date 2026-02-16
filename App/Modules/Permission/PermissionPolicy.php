<?php

declare(strict_types=1);

namespace App\Modules\Permission;

use App\Core\Abstracts\Policy;

class PermissionPolicy extends Policy {
   /**
    * Listeleme yetkisi
    */
   public function viewAny(?array $user): bool {
      return $this->hasPermission($user, 'permission:viewAny');
   }

   /**
    * Görüntüleme yetkisi
    */
   public function view(?array $user, mixed $model = null): bool {
      return $this->hasPermission($user, 'permission:view');
   }

   /**
    * Oluşturma yetkisi
    */
   public function create(?array $user): bool {
      return $this->hasPermission($user, 'permission:create');
   }

   /**
    * Güncelleme yetkisi
    */
   public function update(?array $user, mixed $model = null): bool {
      return $this->hasPermission($user, 'permission:update');
   }

   /**
    * Silme yetkisi
    */
   public function delete(?array $user, mixed $model = null): bool {
      return $this->hasPermission($user, 'permission:delete');
   }
}
