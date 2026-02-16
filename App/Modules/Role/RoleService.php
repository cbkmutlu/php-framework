<?php

declare(strict_types=1);

namespace App\Modules\Role;

use System\Database\Database;
use System\Exception\SystemException;
use System\Gate\Gate;
use App\Core\Abstracts\Service;
use App\Modules\Role\{RoleRepository, RoleRequest};

class RoleService extends Service {
   /** @var RoleRepository */
   protected mixed $repository;

   public function __construct(
      protected Database $database,
      protected Gate $gate,
      RoleRepository $repository
   ) {
      $this->repository = $repository;
   }

   /**
    * getAll
    */
   public function getAll(): array {
      return $this->repository->findAll();
   }

   /**
    * getOne
    */
   public function getOne(int $id): array {
      $result = $this->repository->findOneWithPermission($id);

      if (empty($result)) {
         throw new SystemException('Role not found', 404);
      }

      return $result;
   }

   /**
    * create
    */
   public function createRole(RoleRequest $request): array {
      return $this->repository->transaction(function () use ($request): array {
         // check
         $this->checkFields($request);

         // create
         $create = $this->repository->create([
            'name'        => $request->name,
            'slug'        => $request->slug,
            'description' => $request->description
         ]);

         $roleId = $create->lastInsertId();

         // sync permissions
         if (!empty($request->permissions)) {
            $this->repository->syncPermission($roleId, $request->permissions);
         }

         return $this->getOne($roleId);
      });
   }

   /**
    * update
    */
   public function updateRole(RoleRequest $request): array {
      return $this->repository->transaction(function () use ($request): array {
         // check
         $this->checkFields($request, false);

         // update
         $update = $request->filterArray([
            'name'        => $request->name,
            'slug'        => $request->slug,
            'description' => $request->description
         ]);
         $this->repository->update($update, [
            'id' => $request->id
         ]);

         // sync permissions
         if (isset($request->permissions)) {
            $this->repository->syncPermission($request->id, $request->permissions);
            $this->clearCacheForRole($request->id);
         }

         return $this->getOne($request->id);
      });
   }

   /**
    * delete (hard delete)
    */
   public function deleteRole(int $id): bool {
      return $this->repository->transaction(function () use ($id): bool {
         // check if role exists
         $this->getOne($id);

         // check if role has users assigned
         $userRole = $this->repository->findBy([
            'role_id' => $id
         ], 'app_user_role');
         if ($userRole) {
            throw new SystemException('Role has users assigned', 400);
         }

         // hard delete (FK cascade will handle role_permission)
         $this->repository->hardDelete([
            'id' => $id
         ]);

         return true;
      });
   }

   /**
    * syncPermission
    */
   public function syncPermission(int $roleId, array $permissions): array {
      // resolve permission IDs or slugs to IDs
      $permissionIds = [];
      foreach ($permissions as $permission) {
         $permissionIds[] = $this->resolvePermission($permission);
      }

      return $this->repository->transaction(function () use ($roleId, $permissionIds): array {
         // check if role exists
         $this->getOne($roleId);

         // sync
         $this->repository->syncPermission($roleId, $permissionIds);

         $this->clearCacheForRole($roleId);
         return $this->getOne($roleId);
      });
   }

   /**
    * givePermission
    */
   public function givePermission(int $roleId, int|string $permission): array {
      $permission = $this->resolvePermission($permission);

      return $this->repository->transaction(function () use ($roleId, $permission): array {
         // check if role exists
         $this->getOne($roleId);

         // check if already assigned
         $exists = $this->repository->findBy([
            'role_id'       => $roleId,
            'permission_id' => $permission
         ], 'app_role_permission');
         if ($exists) {
            throw new SystemException('Permission already assigned to this role', 400);
         }

         // give
         $this->repository->givePermission($roleId, $permission);

         $this->clearCacheForRole($roleId);
         return $this->getOne($roleId);
      });
   }

   /**
    * revokePermission
    */
   public function revokePermission(int $roleId, int|string $permission): array {
      $permission = $this->resolvePermission($permission);

      return $this->repository->transaction(function () use ($roleId, $permission): array {
         // check if role exists
         $this->getOne($roleId);

         // check if assigned
         $exists = $this->repository->findBy([
            'role_id'       => $roleId,
            'permission_id' => $permission
         ], 'app_role_permission');
         if (!$exists) {
            throw new SystemException('Permission is not assigned to this role', 400);
         }

         // revoke
         $this->repository->revokePermission($roleId, $permission);

         $this->clearCacheForRole($roleId);
         return $this->getOne($roleId);
      });
   }

   private function clearCacheForRole(int $roleId): void {
      $users = $this->repository->findUserByRoleId($roleId);

      foreach ($users as $user) {
         $this->gate->clearUserCache((int) $user['user_id']);
      }
   }

   /**
    * check
    */
   private function checkFields(RoleRequest $request, bool $create = true): void {
      $this->check([
         'slug' => $request->slug
      ], $request, $create);
   }
}
