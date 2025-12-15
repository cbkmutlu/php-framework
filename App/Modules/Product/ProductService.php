<?php

declare(strict_types=1);

namespace App\Modules\Product;

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
   public function getOne(int $id): array {
      $result = $this->repository->findOne($id);

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
               'sort_order' => $request->sort_order
            ]);

            // create relation
            $product = $this->repository->findOne($create->lastInsertId());
            if (isset($request->product_category)) {
               $this->createRelation($product, $request);
            }

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

            // find image
            $product = $this->repository->findOne($request->id);
            $image = $product['image_path'] ?? null;

            // filter list
            $update = $request->filterArray([
               'code'       => $request->code,
               'title'      => $request->title,
               'content'    => $request->content,
               'is_active'  => $request->is_active,
               'sort_order' => $request->sort_order
            ]);

            // update product
            $this->repository->update($update, [
               'id' => $request->id
            ]);

            // update relation
            $this->updateRelation($product, $request);

            // unlink image
            if (isset($request->image_path)) {
               $this->unlink($image);
            }


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
   public function deleteProduct(int $id): bool {
      return $this->repository->transaction(function () use ($id): bool {
         $image = $this->repository->findOne($id)['image'] ?? null;

         $this->repository->softDelete([
            'id' => $id,
            'deleted_at' => ['IS NULL']
         ]);

         $this->repository->hardDelete([
            'product_id' => $id
         ], 'product_category');

         // unlink image
         if (isset($image)) {
            return $this->unlink($image);
         }

         return true;
      });
   }

   /**
    * upload image
    */
   public function uploadImage(array $files): array {
      return $this->upload($files, 'product');
   }

   /**
    * delete image
    */
   public function deleteImage(int $id): bool {
      return false;
      // return $this->repository->transaction(function () use ($id): bool {
      //    // find image
      //    $image = $this->repository->findOne($id)['image_path'] ?? null;

      //    // update field to null
      //    $this->repository->update([
      //       'image_path' => null
      //    ], [
      //       'id' => $id
      //    ]);

      //    // unlink image
      //    if (isset($image)) {
      //       return $this->unlink($image);
      //    }

      //    return true;
      // });
   }

   /**
    * get relation
    */
   private function getRelation(array $product): array {
      $productId = $product['id'];
      $product['category_list'] = $this->repository->findCategory($productId);
      $product['image_list'] = $this->repository->findImage($productId);
      return $product;
   }

   /**
    * create relation
    */
   // private function createRelation(ProductRequest $request, int $productId): void {
   //    if (is_array($request->product_category)) {
   //       foreach ($request->product_category as $categoryId) {
   //          $category = $this->repository->create([
   //             'product_id' => $productId,
   //             'category_id' => $categoryId
   //          ], 'product_category');

   //          if ($category->affectedRows() <= 0) {
   //             throw new SystemException('Category relation not created', 400);
   //          }
   //       }
   //    }

   //    if (is_array($request->image_path)) {
   //       foreach ($request->image_path as $imagePath) {
   //          $image = $this->repository->create([
   //             'product_id' => $productId,
   //             'image_path' => $imagePath
   //          ], 'product_image');

   //          if ($image->affectedRows() <= 0) {
   //             throw new SystemException('Image relation not created', 400);
   //          }
   //       }
   //    }
   // }
   /**
    * create relation
    */
   private function createRelation(array $product, ProductRequest $request): void {
      // category relation
      $category_diff = array_values(array_diff($request->product_category ?? [], array_column($product['category_list'] ?? [], 'id')));
      if (!empty($category_diff)) {
         $fields = array_map(function ($categoryId) use ($product) {
            return [
               'product_id' => $product['id'],
               'category_id' => $categoryId,
            ];
         }, $category_diff);

         // create
         $this->repository->create($fields, 'product_category');
      }

      // image relation
      $image_diff = array_values(array_diff($request->image_path ?? [], array_column($product['image_list'] ?? [], 'id')));
      if (!empty($image_diff)) {
         $fields = array_map(function ($imagePath) use ($product) {
            return [
               'product_id' => $product['id'],
               'image_path' => $imagePath,
            ];
         }, $image_diff);

         // create
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
      $category_diff = array_values(array_diff(array_column($product['category_list'] ?? [], 'id'), $request->product_category ?? []));
      if (!empty($category_diff)) {
         $this->repository->hardDelete([
            'product_id' => $product['id'],
            'category_id' => ['IN', $category_diff]
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
