<?php

declare(strict_types=1);

namespace App\Core\Abstracts;

abstract class Policy {
   /**
    * Ön kontrol - ability kontrolünden önce çalışır
    *
    * Super Admin bypass gibi global kurallar için kullanılır.
    *
    * @return bool|null true=izin ver, false=reddet, null=ability kontrolüne devam et
    */
   public function before(?array $user, string $ability): ?bool {
      // Super Admin bypass (role slug: super-admin)
      if ($user && in_array('super-admin', $user['roles'] ?? [], true)) {
         return true;
      }

      return null;
   }

   /**
    * Son kontrol - ability kontrolünden sonra çalışır
    *
    * @return bool|null null dönerse ability sonucu kullanılır
    */
   public function after(?array $user, string $ability, bool $result): ?bool {
      return null;
   }

   /**
    * Listeleme yetkisi
    */
   public function viewAny(?array $user): bool {
      return false;
   }

   /**
    * Görüntüleme yetkisi
    */
   public function view(?array $user, mixed $model = null): bool {
      return false;
   }

   /**
    * Oluşturma yetkisi
    */
   public function create(?array $user): bool {
      return false;
   }

   /**
    * Güncelleme yetkisi
    */
   public function update(?array $user, mixed $model = null): bool {
      return false;
   }

   /**
    * Silme yetkisi
    */
   public function delete(?array $user, mixed $model = null): bool {
      return false;
   }

   /**
    * Kullanıcının yetkisi var mı
    *
    * @example $this->hasPermission($user, 'admin');
    */
   protected function hasPermission(?array $user, string $permission): bool {
      if (!$user) {
         return false;
      }
      return in_array($permission, $user['permissions'] ?? [], true);
   }

   /**
    * Kullanıcının herhangi bir yetkisi var mı
    *
    * @example $this->hasAnyPermission($user, ['admin', 'editor']);
    */
   protected function hasAnyPermission(?array $user, array $permissions): bool {
      if (!$user) {
         return false;
      }
      foreach ($permissions as $permission) {
         if (in_array($permission, $user['permissions'] ?? [], true)) {
            return true;
         }
      }
      return false;
   }

   /**
    * Kullanıcının tüm yetkileri var mı
    *
    * @example $this->hasAllPermission($user, ['admin', 'editor']);
    */
   protected function hasAllPermission(?array $user, array $permissions): bool {
      if (!$user) {
         return false;
      }
      foreach ($permissions as $permission) {
         if (!in_array($permission, $user['permissions'] ?? [], true)) {
            return false;
         }
      }
      return true;
   }

   /**
    * Kullanıcının rolü var mı
    *
    * @example $this->hasRole($user, 'admin');
    */
   protected function hasRole(?array $user, string $role): bool {
      if (!$user) {
         return false;
      }
      return in_array($role, $user['roles'] ?? [], true);
   }

   /**
    * Kullanıcının herhangi bir rolü var mı
    *
    * @example $this->hasAnyRole($user, ['admin', 'editor']);
    */
   protected function hasAnyRole(?array $user, array $roles): bool {
      if (!$user) {
         return false;
      }
      foreach ($roles as $role) {
         if (in_array($role, $user['roles'] ?? [], true)) {
            return true;
         }
      }
      return false;
   }

   /**
    * Kullanıcının tüm rolleri var mı
    *
    * @example $this->hasAllRole($user, ['admin', 'editor']);
    */
   protected function hasAllRole(?array $user, array $roles): bool {
      if (!$user) {
         return false;
      }
      foreach ($roles as $role) {
         if (!in_array($role, $user['roles'] ?? [], true)) {
            return false;
         }
      }
      return true;
   }
}
