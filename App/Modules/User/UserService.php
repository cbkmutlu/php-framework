<?php

declare(strict_types=1);

namespace App\Modules\User;

use System\Crypt\Crypt;
use System\Exception\SystemException;
use System\Gate\Gate;
use App\Core\Abstracts\Service;
use App\Modules\User\{UserRepository, UserRequest};

class UserService extends Service {
    public function __construct(
        protected Crypt $crypt,
        protected Gate $gate,
        protected UserRepository $repository
    ) {
    }

    /**
     * Return all users
     */
    public function getAll(): array {
        return $this->repository->findAll();
    }

    /**
     * Return user by id
     */
    public function getOne(int $userId): array {
        $result = $this->repository->findOne($userId);

        if (empty($result)) {
            throw new SystemException('User not found', 404);
        }

        $result['roles'] = $this->repository->findUserRole($userId);
        $result['permissions'] = $this->repository->findUserPermission($userId);

        return $result;
    }

    /**
     * Create user
     */
    public function createUser(UserRequest $request): array {
        return $this->repository->transaction(function () use ($request): array {
            // check fields
            $this->checkFields($request);

            // hash password
            $password = $this->crypt->hash($request->password);

            // create user
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
                $this->syncRole($userId, $request->roles);
            }

            // return created user
            return $this->getOne($userId);
        });
    }

    /**
     * Update user
     */
    public function updateUser(UserRequest $request): array {
        return $this->repository->transaction(function () use ($request): array {
            // check fields
            $this->checkFields($request, false);

            // filter only request properties
            $update = $request->filter([
                'name'    => $request->name,
                'surname' => $request->surname,
                'email'   => $request->email,
                'status'  => $request->status
            ]);

            // hash password if provided
            if (!empty($request->password)) {
                $update['password'] = $this->crypt->hash($request->password);
            }

            // update user
            $this->repository->update($update, [
                'id' => $request->id
            ]);

            // sync roles
            if (isset($request->roles)) {
                $this->syncRole($request->id, $request->roles);
                $this->gate->clearUserCache($request->id);
            }

            // return updated user
            return $this->getOne($request->id);
        });
    }

    /**
     * Delete user (soft delete)
     */
    public function deleteUser(int $userId): bool {
        return $this->repository->transaction(function () use ($userId): bool {
            // soft delete
            $this->repository->softDelete([
                'id'         => $userId,
                'deleted_at' => ['IS NULL']
            ]);

            return true;
        });
    }

    /**
     * Sync role
     */
    public function syncRole(int $userId, array $roles): array {
        return $this->repository->transaction(function () use ($userId, $roles): array {
            // delete all roles
            $this->repository->hardDelete([
                'user_id' => $userId
            ], 'app_user_role');

            // create roles (bulk)
            $create = [];
            foreach ($roles as $role) {
                $create[] = [
                    'user_id'    => $userId,
                    'role_id'    => $this->resolveRole($role),
                    'scope_type' => $role->scope_type,
                    'scope_id'   => $role->scope_id
                ];
            }
            $this->repository->create($create, 'app_user_role');

            // clear cache
            $this->gate->clearUserCache($userId);

            // return updated user
            return $this->getOne($userId);
        });
    }

    /**
     * Give role to user
     */
    public function giveRole(int $userId, int|string $role, ?string $scopeType = null, ?int $scopeId = null): array {
        // resolve role
        $roleId = $this->resolveRole($role);

        return $this->repository->transaction(function () use ($userId, $roleId, $scopeType, $scopeId): array {
            // check relation
            if ($this->repository->hasRoleRelation($userId, $roleId, $scopeType, $scopeId)) {
                throw new SystemException('Role already assigned to this user', 400);
            }

            // give role and clear cache
            $this->repository->create([
                'user_id'    => $userId,
                'role_id'    => $roleId,
                'scope_type' => $scopeType,
                'scope_id'   => $scopeId
            ], 'app_user_role');
            $this->gate->clearUserCache($userId);

            // return updated user
            return $this->getOne($userId);
        });
    }

    /**
     * Revoke role from user
     */
    public function revokeRole(int $userId, int|string $role, ?string $scopeType = null, ?int $scopeId = null): array {
        // resolve role
        $roleId = $this->resolveRole($role);

        return $this->repository->transaction(function () use ($userId, $roleId, $scopeType, $scopeId): array {
            // check relation
            if ($this->repository->hasRoleRelation($userId, $roleId, $scopeType, $scopeId) === false) {
                throw new SystemException('Role not assigned to this user', 400);
            }

            // revoke role
            $this->repository->hardDelete(array_filter([
                'user_id'    => $userId,
                'role_id'    => $roleId,
                'scope_type' => $scopeType,
                'scope_id'   => $scopeId
            ], function ($value) {
                return $value !== null;
            }), 'app_user_role');

            // clear cache
            $this->gate->clearUserCache($userId);

            // return updated user
            return $this->getOne($userId);
        });
    }

    /**
     * Sync permissions for user
     */
    public function syncPermission(int $userId, array $permissions): array {
        return $this->repository->transaction(function () use ($userId, $permissions): array {
            // delete all permissions
            $this->repository->hardDelete([
                'user_id' => $userId
            ], 'app_user_permission');

            // create permissions (bulk)
            $create = [];
            foreach ($permissions as $permission) {
                $create[] = [
                    'user_id'       => $userId,
                    'permission_id' => $this->resolvePermission($permission),
                    'type'          => $permission['type'],
                    'scope_type'    => $permission['scope_type'] ?? null,
                    'scope_id'      => $permission['scope_id'] ?? null
                ];
            }
            $this->repository->create($create, 'app_user_permission');

            // clear cache
            $this->gate->clearUserCache($userId);

            // return updated user
            return $this->getOne($userId);
        });
    }

    /**
     * Give permission to user
     */
    public function givePermission(int $userId, int|string $permission, string $type = 'allow', ?string $scopeType = null, ?int $scopeId = null): array {
        // resolve permission
        $permissionId = $this->resolvePermission($permission);

        return $this->repository->transaction(function () use ($userId, $permissionId, $type, $scopeType, $scopeId): array {
            // check relation
            if ($this->repository->hasPermissionRelation($userId, $permissionId, $scopeType, $scopeId)) {
                throw new SystemException('Permission already assigned to this user', 400);
            }

            // give permission
            $this->repository->create([
                'user_id'       => $userId,
                'permission_id' => $permissionId,
                'type'          => $type,
                'scope_type'    => $scopeType,
                'scope_id'      => $scopeId
            ], 'app_user_permission');

            // clear cache
            $this->gate->clearUserCache($userId);

            // return updated user
            return $this->getOne($userId);
        });
    }

    /**
     * Revoke permission from user
     */
    public function revokePermission(int $userId, int|string $permission, ?string $scopeType = null, ?int $scopeId = null): array {
        // resolve permission
        $permissionId = $this->resolvePermission($permission);

        return $this->repository->transaction(function () use ($userId, $permissionId, $scopeType, $scopeId): array {
            // revoke permission
            $this->repository->hardDelete(array_filter([
                'user_id'       => $userId,
                'permission_id' => $permissionId,
                'scope_type'    => $scopeType,
                'scope_id'      => $scopeId
            ], function ($value) {
                return $value !== null;
            }), 'app_user_permission');

            // clear cache
            $this->gate->clearUserCache($userId);

            // return updated user
            return $this->getOne($userId);
        });
    }

    public function getPermission(int $userId): array {
        $data = [
            'id' => $userId,
            'roles' => [],
            'permissions' => []
        ];

        $userRoles = $this->repository->findUserRole($userId);
        $userPermissions = $this->repository->findUserPermission($userId);
        $rolePermissions = $this->repository->findRolePermission($userId);

        $data['roles'] = $userRoles;
        $data['permissions'] = array_merge($rolePermissions, $userPermissions);

        return $data;
    }

    /**
     * Check fields
     */
    private function checkFields(UserRequest $request, bool $create = true): void {
        $this->check($this->repository, [
            'email' => $request->email
        ], $request, $create);
    }

    /**
     * Resolve role
     */
    private function resolveRole(int|string $role): int {
        if (is_numeric($role)) {
            return (int) $role;
        }

        $role = $this->repository->findBy([
            'slug' => $role
        ], 'app_role');
        if (!$role) {
            throw new SystemException('Role ' . $role . ' not found', 404);
        }

        return $role['id'];
    }

    /**
     * resolvePermission
     */
    private function resolvePermission(int|string $permission): int {
        if (is_numeric($permission)) {
            return (int) $permission;
        }

        $permission = $this->repository->findBy([
            'slug' => $permission
        ], 'app_permission');
        if (!$permission) {
            throw new SystemException('Permission ' . $permission . ' not found', 404);
        }

        return $permission['id'];
    }
}
