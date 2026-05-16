<?php

declare(strict_types=1);

namespace App\Modules\Role;

use System\Exception\SystemException;
use System\Gate\Gate;
use App\Core\Abstracts\Service;
use App\Modules\Permission\PermissionRepository;
use App\Modules\Role\{RoleRepository, RoleRequest};

class RoleService extends Service {
    public function __construct(
        protected Gate $gate,
        protected RoleRepository $repository,
        protected PermissionRepository $permissionRepository
    ) {
    }

    /**
     * Return all roles
     */
    public function getAll(): array {
        return $this->repository->findAll();
    }

    /**
     * Return role by id
     */
    public function getOne(int $roleId): array {
        $result = $this->repository->findOne($roleId);

        if (empty($result)) {
            throw new SystemException('Record not found', 404);
        }

        return $result;
    }

    /**
     * Create role
     */
    public function createRole(RoleRequest $request): array {
        return $this->repository->transaction(function () use ($request): array {
            // check fields
            $this->checkFields($request);

            // create role
            $create = $this->repository->create([
                'name'        => $request->name,
                'slug'        => $request->slug,
                'description' => $request->description
            ]);
            $roleId = $create->lastInsertId();

            // sync permissions
            if (!empty($request->permissions)) {
                $this->syncPermission($roleId, $request->permissions);
            }

            // return created role
            return $this->getOne($roleId);
        });
    }

    /**
     * Update role
     */
    public function updateRole(RoleRequest $request): array {
        return $this->repository->transaction(function () use ($request): array {
            // check fields
            $this->checkFields($request, false);

            // filter only request properties
            $update = $request->filter([
                'name'        => $request->name,
                'slug'        => $request->slug,
                'description' => $request->description
            ]);

            // update role
            $this->repository->update($update, [
                'id' => $request->id
            ]);

            // sync permissions
            if (isset($request->permissions)) {
                $this->syncPermission($request->id, $request->permissions);

                // clear cache
                $this->clearRoleCache($request->id);
            }

            // return updated role
            return $this->getOne($request->id);
        });
    }

    /**
     * Delete role (hard delete)
     */
    public function deleteRole(int $roleId): bool {
        return $this->repository->transaction(function () use ($roleId): bool {
            // check relation
            if ($this->repository->hasUserRelation($roleId)) {
                throw new SystemException('Role has assigned users', 400);
            }

            // hard delete (FK cascade delete role_permission)
            $this->repository->hardDelete([
                'id' => $roleId
            ]);

            return true;
        });
    }

    /**
     * Sync permission for role
     */
    public function syncPermission(int $roleId, array $permissions): array {
        return $this->repository->transaction(function () use ($roleId, $permissions): array {
            // delete all permissions
            $this->repository->hardDelete([
                'role_id' => $roleId
            ], 'app_role_permission');

            // create permissions (bulk)
            $create = [];
            foreach ($permissions as $permission) {
                $create[] = [
                    'role_id'       => $roleId,
                    'permission_id' => $this->resolvePermission($permission)
                ];
            }
            $this->repository->create($create, 'app_role_permission');

            // clear cache
            $this->clearRoleCache($roleId);

            // return updated role
            return $this->getOne($roleId);
        });
    }

    /**
     * Give permission to role
     */
    public function givePermission(int $roleId, int|string $permission): array {
        // resolve permission
        $permission = $this->resolvePermission($permission);

        return $this->repository->transaction(function () use ($roleId, $permission): array {
            // check relation
            if ($this->repository->hasPermissionRelation($roleId, $permission)) {
                throw new SystemException('Permission already assigned to this role', 400);
            }

            // give permission
            $this->repository->create([
                'role_id'       => $roleId,
                'permission_id' => $permission
            ], 'app_role_permission');

            // clear cache
            $this->clearRoleCache($roleId);

            // return updated role
            return $this->getOne($roleId);
        });
    }

    /**
     * Revoke permission from role
     */
    public function revokePermission(int $roleId, int|string $permission): array {
        // resolve permission
        $permission = $this->resolvePermission($permission);

        return $this->repository->transaction(function () use ($roleId, $permission): array {
            // check relation
            if ($this->repository->hasPermissionRelation($roleId, $permission) === false) {
                throw new SystemException('Permission is not assigned to this role', 400);
            }

            // revoke permission
            $this->repository->hardDelete([
                'role_id'       => $roleId,
                'permission_id' => $permission
            ], 'app_role_permission');

            // clear cache
            $this->clearRoleCache($roleId);

            // return updated role
            return $this->getOne($roleId);
        });
    }

    /**
     * Clear role cache
     */
    private function clearRoleCache(int $roleId): void {
        $users = $this->repository->findUserByRole($roleId);

        foreach ($users as $user) {
            $this->gate->clearUserCache((int) $user['user_id']);
        }
    }

    /**
     * Check fields
     */
    private function checkFields(RoleRequest $request, bool $create = true): void {
        $this->check($this->repository, [
            'slug' => $request->slug
        ], $request, $create);
    }

    /**
     * Resolve permission (id or slug)
     */
    private function resolvePermission(int|string $permission): int {
        if (is_numeric($permission)) {
            return (int) $permission;
        }

        $permission = $this->repository->findPermissionBySlug($permission);
        if ($permission === null) {
            throw new SystemException('Permission ' . $permission . ' not found', 404);
        }

        return $permission['id'];
    }
}
