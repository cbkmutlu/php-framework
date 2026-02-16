<?php

declare(strict_types=1);

namespace App\Modules\Permission;

use System\Database\Database;
use System\Exception\SystemException;
use App\Core\Abstracts\Service;
use App\Modules\Permission\{PermissionRepository, PermissionRequest};

class PermissionService extends Service {
   /** @var PermissionRepository */
   protected mixed $repository;

   public function __construct(
      protected Database $database,
      PermissionRepository $repository
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
      $result = $this->repository->findOne($id);

      if (empty($result)) {
         throw new SystemException('Permission not found', 404);
      }

      return $result;
   }

   /**
    * create
    */
   public function createPermission(PermissionRequest $request): array {
      return $this->repository->transaction(function () use ($request): array {
         // check
         $this->checkFields($request);

         // create
         $create = $this->repository->create([
            'name'        => $request->name,
            'slug'        => $request->slug,
            'group_name'  => $request->group_name,
            'description' => $request->description
         ]);

         return $this->getOne($create->lastInsertId());
      });
   }

   /**
    * update
    */
   public function updatePermission(PermissionRequest $request): array {
      return $this->repository->transaction(function () use ($request): array {
         // check
         $this->checkFields($request, false);

         // update
         $update = $request->filterArray([
            'name'        => $request->name,
            'slug'        => $request->slug,
            'group_name'  => $request->group_name,
            'description' => $request->description
         ]);
         $this->repository->update($update, [
            'id' => $request->id
         ]);

         return $this->getOne($request->id);
      });
   }

   /**
    * delete (hard delete)
    */
   public function deletePermission(int $id): bool {
      return $this->repository->transaction(function () use ($id): bool {
         // check if permission exists
         $this->getOne($id);

         // hard delete (FK cascade will handle relationships)
         $this->repository->hardDelete([
            'id' => $id
         ]);

         return true;
      });
   }

   /**
    * getGrouped
    */
   public function getGrouped(): array {
      $permissions = $this->repository->findAllBy([], [
         'group_name' => 'ASC',
         'name'       => 'ASC'
      ]);

      $grouped = [];
      foreach ($permissions as $permission) {
         $grouped[$permission['group_name'] ?? 'Other'][] = $permission;
      }

      return $grouped;
   }

   /**
    * check
    */
   private function checkFields(PermissionRequest $request, bool $create = true): void {
      $this->check([
         'slug' => $request->slug
      ], $request, $create);
   }
}
