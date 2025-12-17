<?php

declare(strict_types=1);

namespace App\Modules\Brand;

use System\Database\Database;
use App\Core\Abstracts\Service;
use System\Exception\SystemException;
use App\Modules\Brand\BrandRequest;
use App\Modules\Brand\BrandRepository;

class BrandService extends Service {
   /** @var BrandRepository */
   protected mixed $repository;

   public function __construct(
      protected Database $database,
      BrandRepository $repository
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
   public function getOne(int $brandId): array {
      $result = $this->repository->findOne($brandId);

      if (empty($result)) {
         throw new SystemException('Record not found', 404);
      }

      return $result;
   }

   /**
    * create
    */
   public function createBrand(BrandRequest $request): array {
      return $this->repository->transaction(function () use ($request): array {
         // check
         $this->checkFields($request);

         // create
         $create = $this->repository->create([
            'title' => $request->title,
            'content' => $request->content,
            'is_active' => $request->is_active,
            'sort_order' => $request->sort_order
         ]);

         return $this->getOne($create->lastInsertId());
      });
   }

   /**
    * update
    */
   public function updateBrand(BrandRequest $request): array {
      return $this->repository->transaction(function () use ($request): array {
         // check
         $this->checkFields($request, false);

         // update
         $update = $request->filterArray([
            'title' => $request->title,
            'content' => $request->content,
            'is_active' => $request->is_active,
            'sort_order' => $request->sort_order
         ]);
         $this->repository->update($update, ['id' => $request->id]);

         return $this->getOne($request->id);
      });
   }

   /**
    * delete
    */
   public function deleteBrand(int $brandId): bool {
      return $this->repository->transaction(function () use ($brandId): bool {
         // check relation with product
         $relation = $this->repository->findBy([
            'brand_id' => $brandId,
            'deleted_at' => ['IS NULL']
         ], 'product');
         if ($relation) {
            throw new SystemException('Brand is related with product', 400);
         }

         // delete brand
         $this->repository->softDelete([
            'id' => $brandId,
            'deleted_at' => ['IS NULL']
         ]);

         return true;
      });
   }

   /**
    * check
    */
   private function checkFields(BrandRequest $request, bool $create = true): void {
      $this->check([
         'title' => $request->title
      ], $request, $create);
   }
}
