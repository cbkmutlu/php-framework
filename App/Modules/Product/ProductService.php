<?php

declare(strict_types=1);

namespace App\Modules\Product;

use System\Database\Database;
use System\Validation\Validation;
use App\Core\Abstracts\BaseService;
use System\Exception\SystemException;
use App\Modules\Product\ProductRequest;
use App\Modules\Product\ProductRepository;

class ProductService extends BaseService {
   /** @var ProductRepository */
   protected mixed $repository;

   public function __construct(
      protected Database $database,
      protected Validation $validation,
      ProductRepository $repository,
   ) {
      $this->repository = $repository;
   }

   public function getAll(): array {
      $result = $this->repository->findAll();

      return array_map(function ($item) {
         $item['category_list'] = $this->repository->findCategory($item['id']);
         $item['image_list'] = $this->repository->findImage($item['id']);
         return $item;
      }, $result);
   }

   public function getOne(int $id): array {
      $result = $this->repository->findOne($id);

      if (empty($result)) {
         throw new SystemException('Record not found', 404);
      }

      $result['category_list'] = $this->repository->findCategory($result['id']);
      $result['image_list'] = $this->repository->findImage($result['id']);

      return $result;
   }

   public function createProduct(ProductRequest $dto): array {
      return $this->transaction(function () use ($dto) {
         $this->validate($dto->toArray(), [
            'code' => 'required',
            'title' => 'required',
            'is_active' => 'required|numeric',
            'sort_order' => 'required|numeric',
            'product_category' => 'required|must_be_array'
         ]);

         $id = $this->create([
            'code' => $dto->code,
            'title' => $dto->title,
            'content' => $dto->content,
            'is_active' => $dto->is_active,
            'sort_order' => $dto->sort_order,
         ]);

         $this->createRelation($dto, $id);

         return $this->getOne($id);
      });
   }

   public function updateProduct(ProductRequest $dto): array {
      return $this->transaction(function () use ($dto) {
         $this->check([
            'id' => $dto->id
         ]);

         $this->validate($dto->toArray(), [
            'id' => 'required|numeric',
            'title' => 'required',
            'is_active' => 'required|numeric',
            'sort_order' => 'required|numeric'
         ]);

         $this->update($dto, [
            'code' => $dto->code,
            'title' => $dto->title,
            'content' => $dto->content,
            'is_active' => $dto->is_active,
            'sort_order' => $dto->sort_order,
         ], [
            'id' => $dto->id
         ]);

         if (isset($dto->image_path) && is_array($dto->image_path)) {
            foreach ($dto->image_path as $path) {
               $this->create([
                  'product_id' => $dto->id,
                  'image_path' => $path
               ], 'product_image');
            }
         }

         $this->updateRelation($dto, [
            'product_category'
         ]);

         return $this->getOne($dto->id);
      });
   }

   private function createRelation(ProductRequest $dto, int $id): void {
      if (isset($dto->product_category) && is_array($dto->product_category)) {
         foreach ($dto->product_category as $category_id) {
            $category = $this->repository->create([
               'product_id' => $id,
               'category_id' => $category_id
            ], 'product_category');

            if ($category->affectedRows() <= 0) {
               throw new SystemException('Category relation not created', 400);
            }
         }
      }
   }

   private function updateRelation(ProductRequest $dto, array $tables): void {
      foreach ($tables as $table) {
         if (isset($dto->$table) && is_array($dto->$table)) {
            $this->repository->hardDelete([
               'product_id' => $dto->id
            ], $table);
         }
      }

      $this->createRelation($dto, $dto->id);
   }
}
