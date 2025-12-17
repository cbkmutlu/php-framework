<?php

declare(strict_types=1);

namespace App\Modules\Product;

use System\Date\Date;
use System\Upload\Upload;
use App\Core\Abstracts\Service;
use System\Exception\SystemException;
use App\Modules\Product\ProductRequest;
use App\Modules\Product\ProductRepository;

class ProductService extends Service {
   /** @var ProductRepository */
   protected mixed $repository;

   public function __construct(
      protected Upload $upload,
      protected Date $date,
      ProductRepository $repository
   ) {
      $this->repository = $repository;
   }

   /**
    * getAll
    */
   public function getAll(): array {
      $result = $this->repository->findAll();

      return array_map(function ($product) {
         $product = $this->getRelation($product);
         return $product;
      }, $result);
   }

   /**
    * getOne
    */
   public function getOne(int $productId): array {
      $result = $this->repository->findOne($productId);

      if (empty($result)) {
         throw new SystemException('Record not found', 404);
      }

      return $this->getRelation($result);
   }

   /**
    * create
    */
   public function createProduct(ProductRequest $request): array {
      return $this->repository->transaction(function () use ($request): array {
         try {
            // check
            $this->checkFields($request);

            // create
            $create = $this->repository->create([
               'code'       => $request->code,
               'title'      => $request->title,
               'content'    => $request->content,
               'is_active'  => $request->is_active,
               'sort_order' => $request->sort_order,
               'brand_id'   => $request->brand_id,
               'stock'      => $request->stock,
               'price'      => $request->price,
               'date'       => $this->date->setDate($request->date)->getDate(Date::GENERIC3)
            ]);

            // create relation
            $product = $this->repository->findOne($create->lastInsertId());
            $this->createRelation($product, $request);

            return $this->getRelation($product);
         } catch (SystemException $e) {
            // unlink image
            if (isset($request->image_path)) {
               $this->unlink($request->image_path);
            }

            throw $e;
         }
      });
   }

   /**
    * update
    */
   public function updateProduct(ProductRequest $request): array {
      return $this->repository->transaction(function () use ($request): array {
         try {
            // check
            $this->checkFields($request, false);

            // get item
            $product = $this->getOne($request->id);

            // filter list
            $update = $request->filterArray([
               'code'       => $request->code,
               'title'      => $request->title,
               'content'    => $request->content,
               'is_active'  => $request->is_active,
               'sort_order' => $request->sort_order,
               'brand_id'   => $request->brand_id,
               'stock'      => $request->stock,
               'price'      => $request->price,
               'date'       => $this->date->setDate($request->date)->getDate(Date::GENERIC3)
            ]);

            // update product
            $this->repository->update($update, [
               'id' => $request->id
            ]);

            // update relation
            $this->updateRelation($product, $request);

            return $this->getOne($request->id);
         } catch (SystemException $e) {
            // unlink image
            if (isset($request->image_path)) {
               $this->unlink($request->image_path);
            }

            throw $e;
         }
      });
   }

   /**
    * delete
    */
   public function deleteProduct(int $productId): bool {
      return $this->repository->transaction(function () use ($productId): bool {
         // get item and find image relation
         $product = $this->getOne($productId);
         $imagePath = array_values(array_column($product['image_list'] ?? [], 'image_path'));

         // delete product
         $this->repository->softDelete([
            'id' => $productId,
            'deleted_at' => ['IS NULL']
         ]);

         // delete image by product_id
         $this->repository->hardDelete([
            'product_id' => $productId
         ], 'product_image');

         // delete category by product_id
         $this->repository->hardDelete([
            'product_id' => $productId
         ], 'product_category');

         // unlink image
         if (isset($imagePath)) {
            return $this->unlink($imagePath);
         }

         return true;
      });
   }

   /**
    * upload image
    */
   public function uploadImage(?array $files): array {
      return $this->upload($files, 'product');
   }

   /**
    * delete image
    */
   public function deleteImage(int $imageId): bool {
      return $this->repository->transaction(function () use ($imageId): bool {
         $image = $this->repository->findOneImage($imageId);
         $imagePath = $image['image_path'] ?? null;

         // delete image
         $this->repository->hardDelete([
            'id' => $imageId
         ], 'product_image');

         // unlink image
         if (isset($imagePath)) {
            return $this->unlink($imagePath);
         }

         return true;
      });
   }

   /**
    * get relation
    */
   private function getRelation(array $product): array {
      $productId = $product['id'];
      $product['category_list'] = $this->repository->findAllCategory($productId);
      $product['image_list'] = $this->repository->findAllImage($productId);
      $product['brand'] = $this->repository->findOneBrand($product['brand_id']) ?: null;
      return $product;
   }

   /**
    * create relation
    */
   private function createRelation(array $product, ProductRequest $request): void {
      // category relation
      $categoryDiff = array_values(array_diff($request->product_category ?? [], array_column($product['category_list'] ?? [], 'id')));
      if (!empty($categoryDiff)) {
         $fields = array_map(function ($categoryId) use ($product) {
            return [
               'product_id' => $product['id'],
               'category_id' => $categoryId,
            ];
         }, $categoryDiff);

         // create category relation
         $this->repository->create($fields, 'product_category');
      }

      // image relation
      $imageDiff = array_values(array_diff($request->image_path ?? [], array_column($product['image_list'] ?? [], 'id')));
      if (!empty($imageDiff)) {
         $fields = array_map(function ($imagePath) use ($product) {
            return [
               'product_id' => $product['id'],
               'image_path' => $imagePath,
            ];
         }, $imageDiff);

         // create image relation
         $this->repository->create($fields, 'product_image');
      }
   }

   /**
    * update relation
    */
   private function updateRelation(array $product, ProductRequest $request): void {
      $this->deleteRelation($product, $request);
      $this->createRelation($product, $request);
   }

   /**
    * delete relation
    */
   private function deleteRelation(array $product, ProductRequest $request): void {
      // category relation
      $categoryDiff = array_values(array_diff(array_column($product['category_list'] ?? [], 'id'), $request->product_category ?? []));
      if (!empty($categoryDiff)) {
         $this->repository->hardDelete([
            'product_id' => $product['id'],
            'category_id' => ['IN', $categoryDiff]
         ], 'product_category');
      }
   }

   /**
    * check
    */
   private function checkFields(ProductRequest $request, bool $create = true): void {
      $this->check([
         'code' => $request->code
      ], $request, $create);
   }
}
