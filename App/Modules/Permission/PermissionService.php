<?php

declare(strict_types=1);

namespace App\Modules\Permission;

use System\Exception\SystemException;
use App\Core\Abstracts\Service;
use App\Modules\Permission\{PermissionRepository, PermissionRequest};

class PermissionService extends Service {
    public function __construct(
        protected PermissionRepository $repository
    ) {
    }

    /**
     * Return all permissions
     */
    public function getAll(): array {
        return $this->repository->findAll();
    }

    /**
     * Return permission by id
     */
    public function getOne(int $permissionId): array {
        $result = $this->repository->findOne($permissionId);

        if (empty($result)) {
            throw new SystemException('Permission not found', 404);
        }

        return $result;
    }

    /**
     * Create permission
     */
    public function createPermission(PermissionRequest $request): array {
        return $this->repository->transaction(function () use ($request): array {
            // check fields
            $this->checkFields($request);

            // create permission
            $create = $this->repository->create([
                'name'        => $request->name,
                'slug'        => $request->slug,
                'group_name'  => $request->group_name,
                'description' => $request->description
            ]);

            // return created permission
            return $this->getOne($create->lastInsertId());
        });
    }

    /**
     * Update permission
     */
    public function updatePermission(PermissionRequest $request): array {
        return $this->repository->transaction(function () use ($request): array {
            // check fields
            $this->checkFields($request, false);

            // filter only request properties
            $update = $request->filter([
                'name'        => $request->name,
                'slug'        => $request->slug,
                'group_name'  => $request->group_name,
                'description' => $request->description
            ]);

            // update permission
            $this->repository->update($update, [
                'id' => $request->id
            ]);

            // return updated permission
            return $this->getOne($request->id);
        });
    }

    /**
     * Delete permission (hard delete)
     */
    public function deletePermission(int $permissionId): bool {
        return $this->repository->transaction(function () use ($permissionId): bool {
            // check relation
            if ($this->repository->hasRoleRelation($permissionId)) {
                throw new SystemException('Permission has assigned roles', 400);
            } elseif ($this->repository->hasUserRelation($permissionId)) {
                throw new SystemException('Permission has assigned users', 400);
            }

            // delete permission (FK cascade will handle relations)
            $this->repository->hardDelete([
                'id' => $permissionId
            ]);

            return true;
        });
    }

    /**
     * Return permissions grouped by group name
     */
    public function getGrouped(): array {
        $permissions = $this->repository->findGrouped();

        $grouped = [];
        foreach ($permissions as $permission) {
            $grouped[$permission['group_name'] ?? 'Other'][] = $permission;
        }

        return $grouped;
    }

    /**
     * Check fields
     */
    private function checkFields(PermissionRequest $request, bool $create = true): void {
        $this->check($this->repository, [
            'slug' => $request->slug
        ], $request, $create);
    }
}
