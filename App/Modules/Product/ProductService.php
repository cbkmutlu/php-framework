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

   /**
    * getAll
    */
   public function getAll(): array {
      $result = $this->repository->findAll();

      return array_map(function ($item) {
         $item['category_list'] = $this->repository->findCategory($item['id']);
         $item['image_list'] = $this->repository->findImage($item['id']);

         return $item;
      }, $result);
   }

   /**
    * getOne
    */
   public function getOne(int $id): array {
      $result = $this->repository->findOne($id);

      if (empty($result)) {
         throw new SystemException('Record not found', 404);
      }

      $result['category_list'] = $this->repository->findCategory($result['id']);
      $result['image_list'] = $this->repository->findImage($result['id']);

      return $result;
   }

   /**
    * create
    */
   public function createProduct(ProductRequest $request): array {
      $this->checkFields($request);

      return $this->transaction(function () use ($request): array {
         $id = $this->create([
            'code' => $request->code,
            'title' => $request->title,
            'content' => $request->content,
            'is_active' => $request->is_active,
            'sort_order' => $request->sort_order,
         ]);

         // create relation
         $this->createRelation($request, $id);

         return $this->getOne($id);
      });
   }

   /**
    * update
    */
   public function updateProduct(ProductRequest $request): array {
      $this->checkFields($request, false);

      return $this->transaction(function () use ($request): array {
         $this->update([
            'code' => $request->code,
            'title' => $request->title,
            'content' => $request->content,
            'is_active' => $request->is_active,
            'sort_order' => $request->sort_order,
         ], $request);

         // update relation
         $this->updateRelation($request, $request->id);

         return $this->getOne($request->id);
      });
   }

   /**
    * create relation
    */
   private function createRelation(ProductRequest $request, int $productId): void {
      if (is_array($request->product_category)) {
         foreach ($request->product_category as $categoryId) {
            $category = $this->repository->create([
               'product_id' => $productId,
               'category_id' => $categoryId
            ], 'product_category');

            if ($category->affectedRows() <= 0) {
               throw new SystemException('Category relation not created', 400);
            }
         }
      }

      if (is_array($request->image_path)) {
         foreach ($request->image_path as $imagePath) {
            $image = $this->repository->create([
               'product_id' => $productId,
               'image_path' => $imagePath
            ], 'product_image');

            if ($image->affectedRows() <= 0) {
               throw new SystemException('Image relation not created', 400);
            }
         }
      }
   }

   /**
    * update relation
    */
   private function updateRelation(ProductRequest $request, int $productId): void {
      if (is_array($request->product_category)) {
         $this->repository->hardDelete([
            'product_id' => $productId
         ], 'product_category');

         $this->createRelation($request, $productId);
      }
   }

   /**
    * check
    */
   private function checkFields(ProductRequest $request, bool $create = true): void {
      try {
         $this->validate([
            'code' => 'required',
            'title' => 'required',
            'is_active' => 'required|numeric',
            'sort_order' => 'required|numeric',
         ], $request);

         $this->check([
            'code' => $request->code
         ], $request, $create);

      } catch (SystemException $e) {
         if ($request->image_path) {
            $this->unlink([
               'path' => $request->image_path
            ]);
         }

         throw $e;
      }
   }
}
