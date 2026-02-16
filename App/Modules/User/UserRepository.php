<?php

declare(strict_types=1);

namespace App\Modules\User;

use System\Database\Database;
use App\Core\Abstracts\Repository;

class UserRepository extends Repository {
   public function __construct(
      protected Database $database,
      protected string $table = 'app_user'
   ) {
   }

   /**
    * Find user with roles and permissions
    */
   public function findOneWithRelations(int $id): array|false {
      $user = $this->findOne($id);

      if (!$user) {
         return false;
      }

      // Remove password from response
      unset($user['password']);

      // Get user roles with scope
      $user['roles'] = $this->database
         ->prepare("SELECT
               r.*,
               ur.scope_type,
               ur.scope_id
            FROM app_role r
            INNER JOIN app_user_role ur ON ur.role_id = r.id
            WHERE ur.user_id = :user_id
            ORDER BY r.name ASC
         ")
         ->execute([
            'user_id' => $id
         ])
         ->fetchAll();

      // Get user direct permissions
      $user['permissions'] = $this->database
         ->prepare("SELECT
               p.*,
               up.type,
               up.scope_type,
               up.scope_id
            FROM app_permission p
            INNER JOIN app_user_permission up ON up.permission_id = p.id
            WHERE up.user_id = :user_id
            ORDER BY p.group_name ASC, p.name ASC
         ")
         ->execute([
            'user_id' => $id
         ])
         ->fetchAll();

      return $user;
   }

   /**
    * Sync roles for a user
    */
   public function syncRole(int $userId, array $roles): void {
      // detach all
      $this->hardDelete([
         'user_id' => $userId
      ], 'app_user_role');

      // attach
      foreach ($roles as $role) {
         $this->create([
            'user_id'    => $userId,
            'role_id'    => $role['role_id'],
            'scope_type' => $role['scope_type'] ?? null,
            'scope_id'   => $role['scope_id'] ?? null
         ], 'app_user_role');
      }
   }

   /**
    * Give a single role to user
    */
   public function giveRole(int $userId, int $roleId, ?string $scopeType = null, ?int $scopeId = null): void {
      $this->create([
         'user_id'    => $userId,
         'role_id'    => $roleId,
         'scope_type' => $scopeType,
         'scope_id'   => $scopeId
      ], 'app_user_role');
   }

   /**
    * Revoke a single role from user
    */
   public function revokeRole(int $userId, int $roleId, ?string $scopeType = null, ?int $scopeId = null): void {
      $conditions = [
         'user_id' => $userId,
         'role_id' => $roleId
      ];

      if ($scopeType !== null) {
         $conditions['scope_type'] = $scopeType;
      }
      if ($scopeId !== null) {
         $conditions['scope_id'] = $scopeId;
      }

      $this->hardDelete($conditions, 'app_user_role');
   }

   /**
    * Sync permissions for a user
    */
   public function syncPermission(int $userId, array $permissions): void {
      // detach all
      $this->hardDelete([
         'user_id' => $userId
      ], 'app_user_permission');

      // attach
      foreach ($permissions as $permission) {
         $this->create([
            'user_id'       => $userId,
            'permission_id' => $permission['permission_id'],
            'type'          => $permission['type'],
            'scope_type'    => $permission['scope_type'] ?? null,
            'scope_id'      => $permission['scope_id'] ?? null
         ], 'app_user_permission');
      }
   }

   /**
    * Give a direct permission to user
    */
   public function givePermission(int $userId, int $permissionId, string $type = 'allow', ?string $scopeType = null, ?int $scopeId = null): void {
      $this->create([
         'user_id'       => $userId,
         'permission_id' => $permissionId,
         'type'          => $type,
         'scope_type'    => $scopeType,
         'scope_id'      => $scopeId
      ], 'app_user_permission');
   }

   /**
    * Revoke a direct permission from user
    */
   public function revokePermission(int $userId, int $permissionId, ?string $scopeType = null, ?int $scopeId = null): void {
      $conditions = [
         'user_id'       => $userId,
         'permission_id' => $permissionId
      ];

      if ($scopeType !== null) {
         $conditions['scope_type'] = $scopeType;
      }
      if ($scopeId !== null) {
         $conditions['scope_id'] = $scopeId;
      }

      $this->hardDelete($conditions, 'app_user_permission');
   }

   /**
    * Load user roles and permissions
    */
   public function getPermission(int $userId): array {
      $data = [
         'id' => $userId,
         'roles' => [],
         'permissions' => []
      ];

      // roles
      $roles = $this->database
         ->prepare("SELECT r.id, r.name, r.slug, ur.scope_type, ur.scope_id
            FROM app_user_role ur
            JOIN app_role r ON r.id = ur.role_id
            WHERE ur.user_id = :user_id
         ")
         ->execute(['user_id' => $userId])
         ->fetchAll();

      $data['roles'] = $roles;

      // role permissions
      $rolePermissions = $this->database
         ->prepare("SELECT p.id, p.name, p.slug, 'allow' AS type, ur.scope_type, ur.scope_id, 'role' AS source
            FROM app_user_role ur
            JOIN app_role_permission rp ON rp.role_id = ur.role_id
            JOIN app_permission p ON p.id = rp.permission_id
            WHERE ur.user_id = :user_id
         ")
         ->execute(['user_id' => $userId])
         ->fetchAll();

      // direct permissions
      $directPermissions = $this->database
         ->prepare("SELECT p.id, p.name, p.slug, up.type, up.scope_type, up.scope_id, 'direct' AS source
            FROM app_user_permission up
            JOIN app_permission p ON p.id = up.permission_id
            WHERE up.user_id = :user_id
         ")
         ->execute(['user_id' => $userId])
         ->fetchAll();

      $data['permissions'] = [
         ...$rolePermissions,
         ...$directPermissions
      ];

      return $data;
   }
}
