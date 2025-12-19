<?php

declare(strict_types=1);

namespace App\Modules\Category;

use System\Upload\Upload;
use App\Core\Abstracts\Service;
use System\Exception\SystemException;
use App\Modules\Category\CategoryRequest;
use App\Modules\Category\CategoryRepository;
use System\Upload\Adapter\LocalUpload;

class CategoryService extends Service {
   /** @var CategoryRepository */
   protected mixed $repository;

   public function __construct(
      protected Upload $upload,
      CategoryRepository $repository
   ) {
      $this->repository = $repository;
   }

   /**
    * getAll
    */
   public function getAll(int $lang_id): array {
      return $this->repository->findAll($lang_id);
   }

   /**
    * getOne
    */
   public function getOne(int $categoryId, int $lang_id): array {
      $result = $this->repository->findOne($categoryId, $lang_id);

      if (empty($result)) {
         throw new SystemException('Record not found', 404);
      }

      return $result;
   }

   /**
    * create
    */
   public function createCategory(CategoryRequest $request, int $lang_id): array {
      return $this->repository->transaction(function () use ($request, $lang_id): array {
         try {
            // check
            $this->checkFields($request);

            // create
            $create = $this->repository->create([
               'code'       => $request->code,
               'is_active'  => $request->is_active,
               'sort_order' => $request->sort_order,
               'image_path' => $request->image_path
            ]);

            return $this->getOne($create->lastInsertId(), $lang_id);
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
   public function updateCategory(CategoryRequest $request, int $lang_id): array {
      return $this->repository->transaction(function () use ($request, $lang_id): array {
         try {
            // check
            $this->checkFields($request, false);

            // get item and find image field
            $category = $this->getOne($request->id, $lang_id);
            $imagePath = $category['image_path'] ?? null;

            // filter list
            $update = $request->filterArray([
               'code'       => $request->code,
               'is_active'  => $request->is_active,
               'sort_order' => $request->sort_order,
               'image_path' => $request->image_path
            ]);

            // update category
            $this->repository->update($update, [
               'id' => $request->id
            ]);

            // update translate
            $this->translate(['title', 'content'], [
               'category_id' => $request->id
            ], $request->translate, 'category_translate');

            // unlink old image
            if (isset($request->image_path)) {
               $this->unlink($imagePath);
            }

            return $this->getOne($request->id, $lang_id);
         } catch (SystemException $e) {
            // unlink request image if error
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
   public function deleteCategory(int $categoryId): bool {
      return $this->repository->transaction(function () use ($categoryId): bool {
         // get item and find image field
         $category = $this->getOne($categoryId, 1);
         $imagePath = $category['image_path'] ?? null;

         // check relation with product
         $relation = $this->repository->findBy([
            'category_id' => $categoryId
         ], 'product_category');
         if ($relation) {
            throw new SystemException('Category is related with product', 400);
         }

         // delete category
         $this->repository->softDelete([
            'id'         => $categoryId,
            'deleted_at' => ['IS NULL']
         ]);

         // unlink image
         if (isset($imagePath)) {
            return $this->unlink($imagePath);
         }

         return true;
      });
   }

   /**
    * updateOrder
    */
   public function updateOrder(array $fields): array {
      return $this->repository->transaction(function () use ($fields): array {
         // update list
         $fields = array_map(function ($item) {
            return [
               'id'         => $item['id'],
               'sort_order' => $item['sort_order']
            ];
         }, $fields);

         // update
         $this->repository->update([
            'sort_order' => ['CASE', 'id', $fields]
         ], [
            'id' => ['IN', array_column($fields, 'id')]
         ]);

         return $this->repository->findAllBy([
            'id' => ['IN', array_column($fields, 'id')]
         ]);
      });
   }

   /**
    * upload image
    */
   public function uploadImage(?array $files): array {
      return $this->upload($files, 'category');
   }

   /**
    * delete image
    */
   public function deleteImage(int $categoryId): bool {
      return $this->repository->transaction(function () use ($categoryId): bool {
         // get item and find image field
         $category = $this->getOne($categoryId, 1);
         $imagePath = $category['image_path'] ?? null;

         // update field to null
         $this->repository->update([
            'image_path' => null
         ], [
            'id' => $categoryId
         ]);

         // unlink image
         if (isset($imagePath)) {
            return $this->unlink($imagePath);
         }

         return true;
      });
   }

   /**
    * check
    */
   private function checkFields(CategoryRequest $request, bool $create = true): void {
      $this->check([
         'code' => $request->code
      ], $request, $create);
   }
}
