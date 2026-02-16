<?php

declare(strict_types=1);

namespace App\Modules\User;

use System\Crypt\Crypt;
use System\Database\Database;
use System\Exception\SystemException;
use System\Gate\Gate;
use App\Core\Abstracts\Service;
use App\Modules\User\{UserRepository, UserRequest};

class UserService extends Service {
   /** @var UserRepository */
   protected mixed $repository;

   public function __construct(
      protected Database $database,
      protected Crypt $crypt,
      protected Gate $gate,
      UserRepository $repository
   ) {
      $this->repository = $repository;
   }

   /**
    * getAll
    */
   public function getAll(): array {
      $users = $this->repository->findAll();

      // Remove password from list
      return array_map(function ($user) {
         unset($user['password']);
         return $user;
      }, $users);
   }

   /**
    * getOne
    */
   public function getOne(int $id): array {
      $result = $this->repository->findOneWithRelations($id);

      if (empty($result)) {
         throw new SystemException('User not found', 404);
      }

      return $result;
   }

   /**
    * create
    */
   public function createUser(UserRequest $request): array {
      return $this->repository->transaction(function () use ($request): array {
         // check
         $this->checkFields($request);

         // hash password
         $password = $this->crypt->hash($request->password);

         // create
         $create = $this->repository->create([
            'name'     => $request->name,
            'surname'  => $request->surname,
            'email'    => $request->email,
            'password' => $password,
            'status'   => $request->status
         ]);

         $userId = $create->lastInsertId();

         // sync roles
         if (!empty($request->roles)) {
            $this->repository->syncRole($userId, $request->roles);
         }

         return $this->getOne($userId);
      });
   }

   /**
    * update
    */
   public function updateUser(UserRequest $request): array {
      return $this->repository->transaction(function () use ($request): array {
         // check
         $this->checkFields($request, false);

         // build update data
         $update = $request->filterArray([
            'name'    => $request->name,
            'surname' => $request->surname,
            'email'   => $request->email,
            'status'  => $request->status
         ]);

         // hash password if provided
         if (!empty($request->password)) {
            $update['password'] = $this->crypt->hash($request->password);
         }

         $this->repository->update($update, [
            'id' => $request->id
         ]);

         // sync roles
         if (isset($request->roles)) {
            $this->repository->syncRole($request->id, $request->roles);
            $this->gate->clearUserCache($request->id);
         }

         return $this->getOne($request->id);
      });
   }

   /**
    * delete (soft delete)
    */
   public function deleteUser(int $id): bool {
      return $this->repository->transaction(function () use ($id): bool {
         // check if user exists
         $this->getOne($id);

         // soft delete
         $this->repository->softDelete([
            'id'         => $id,
            'deleted_at' => ['IS NULL']
         ]);

         return true;
      });
   }

   /**
    * syncRole
    */
   public function syncRole(int $userId, array $roles): array {
      return $this->repository->transaction(function () use ($userId, $roles): array {
         $this->getOne($userId);

         // resolve role slugs to IDs
         foreach ($roles as &$role) {
            if (isset($role['role_id'])) {
               $role['role_id'] = $this->resolveRole($role['role_id']);
            }
         }
         unset($role);

         $this->repository->syncRole($userId, $roles);
         $this->gate->clearUserCache($userId);

         return $this->getOne($userId);
      });
   }

   /**
    * giveRole
    */
   public function giveRole(int $userId, int|string $role, ?string $scopeType = null, ?int $scopeId = null): array {
      $role = $this->resolveRole($role);

      return $this->repository->transaction(function () use ($userId, $role, $scopeType, $scopeId): array {
         $this->getOne($userId);

         // check if already assigned
         $exists = $this->repository->findBy([
            'user_id'    => $userId,
            'role_id'    => $role,
            'scope_type' => $scopeType ?? ['IS NULL'],
            'scope_id'   => $scopeId ?? ['IS NULL']
         ], 'app_user_role');
         if ($exists) {
            throw new SystemException('Role already assigned to this user', 400);
         }

         $this->repository->giveRole($userId, $role, $scopeType, $scopeId);
         $this->gate->clearUserCache($userId);

         return $this->getOne($userId);
      });
   }

   /**
    * revokeRole
    */
   public function revokeRole(int $userId, int|string $role, ?string $scopeType = null, ?int $scopeId = null): array {
      $role = $this->resolveRole($role);

      return $this->repository->transaction(function () use ($userId, $role, $scopeType, $scopeId): array {
         $this->getOne($userId);

         $this->repository->revokeRole($userId, $role, $scopeType, $scopeId);
         $this->gate->clearUserCache($userId);

         return $this->getOne($userId);
      });
   }

   /**
    * syncPermission
    */
   public function syncPermission(int $userId, array $permissions): array {
      return $this->repository->transaction(function () use ($userId, $permissions): array {
         $this->getOne($userId);

         // resolve permission IDs or slugs to IDs
         foreach ($permissions as &$permission) {
            if (isset($permission['permission_id'])) {
               $permission['permission_id'] = $this->resolvePermission($permission['permission_id']);
            }
         }
         unset($permission);

         $this->repository->syncPermission($userId, $permissions);
         $this->gate->clearUserCache($userId);

         return $this->getOne($userId);
      });
   }

   /**
    * givePermission
    */
   public function givePermission(int $userId, int|string $permission, string $type = 'allow', ?string $scopeType = null, ?int $scopeId = null): array {
      $permission = $this->resolvePermission($permission);

      return $this->repository->transaction(function () use ($userId, $permission, $type, $scopeType, $scopeId): array {
         $this->getOne($userId);

         // check if already assigned
         $exists = $this->repository->findBy([
            'user_id'       => $userId,
            'permission_id' => $permission,
            'scope_type'    => $scopeType ?? ['IS NULL'],
            'scope_id'      => $scopeId ?? ['IS NULL']
         ], 'app_user_permission');
         if ($exists) {
            throw new SystemException('Permission already assigned to this user', 400);
         }

         $this->repository->givePermission($userId, $permission, $type, $scopeType, $scopeId);
         $this->gate->clearUserCache($userId);

         return $this->getOne($userId);
      });
   }

   /**
    * revokePermission
    */
   public function revokePermission(int $userId, int|string $permission, ?string $scopeType = null, ?int $scopeId = null): array {
      $permission = $this->resolvePermission($permission);

      return $this->repository->transaction(function () use ($userId, $permission, $scopeType, $scopeId): array {
         $this->getOne($userId);

         $this->repository->revokePermission($userId, $permission, $scopeType, $scopeId);
         $this->gate->clearUserCache($userId);

         return $this->getOne($userId);
      });
   }

   /**
    * check
    */
   private function checkFields(UserRequest $request, bool $create = true): void {
      $this->check([
         'email' => $request->email
      ], $request, $create);
   }
}
