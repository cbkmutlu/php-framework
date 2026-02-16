<?php

declare(strict_types=1);

namespace App\Modules\Role;

use System\Database\Database;
use App\Core\Abstracts\Repository;

class RoleRepository extends Repository {
   public function __construct(
      protected Database $database,
      protected string $table = 'app_role'
   ) {
   }

   /**
    * Find role with its permissions
    */
   public function findOneWithPermission(int $id): array|false {
      $role = $this->findOne($id);

      if (!$role) {
         return false;
      }

      $role['permissions'] = $this->database
         ->prepare("SELECT
               p.*
            FROM app_permission p
            INNER JOIN app_role_permission rp ON rp.permission_id = p.id
            WHERE rp.role_id = :role_id
            ORDER BY p.group_name ASC, p.name ASC
         ")
         ->execute([
            'role_id' => $id
         ])
         ->fetchAll();

      return $role;
   }

   /**
    * Find user IDs by role
    */
   public function findUserByRoleId(int $roleId): array {
      return $this->database
         ->prepare("SELECT user_id FROM app_user_role WHERE role_id = :role_id")
         ->execute(['role_id' => $roleId])
         ->fetchAll();
   }

   /**
    * Sync permissions for a role (detach all, then attach given)
    */
   public function syncPermission(int $roleId, array $permissionIds): void {
      // detach all
      $this->hardDelete([
         'role_id' => $roleId
      ], 'app_role_permission');

      // attach
      foreach ($permissionIds as $permissionId) {
         $this->create([
            'role_id'       => $roleId,
            'permission_id' => $permissionId
         ], 'app_role_permission');
      }
   }

   /**
    * Give a single permission to a role
    */
   public function givePermission(int $roleId, int $permissionId): void {
      $this->create([
         'role_id'       => $roleId,
         'permission_id' => $permissionId
      ], 'app_role_permission');
   }

   /**
    * Revoke a single permission from a role
    */
   public function revokePermission(int $roleId, int $permissionId): void {
      $this->hardDelete([
         'role_id'       => $roleId,
         'permission_id' => $permissionId
      ], 'app_role_permission');
   }
}
