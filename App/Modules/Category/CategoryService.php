<?php

declare(strict_types=1);

namespace App\Modules\Category;

use System\Upload\Upload;
use App\Core\Abstracts\Service;
use System\Exception\SystemException;
use App\Modules\Category\CategoryRequest;
use App\Modules\Category\CategoryRepository;

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
   public function getAll(): array {
      return $this->repository->findAll();
   }

   /**
    * getOne
    */
   public function getOne(int $id): array {
      $result = $this->repository->findOne($id);

      if (empty($result)) {
         throw new SystemException('Record not found', 404);
      }

      return $result;
   }

   /**
    * create
    */
   public function createCategory(CategoryRequest $request): array {
      return $this->repository->transaction(function () use ($request): array {
         try {
            // check
            $this->checkFields($request);

            // create
            $create = $this->repository->create([
               'code'       => $request->code,
               'title'      => $request->title,
               'content'    => $request->content,
               'image_path' => $request->image_path,
               'is_active'  => $request->is_active,
               'sort_order' => $request->sort_order
            ]);

            return $this->getOne($create->lastInsertId());
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
   public function updateCategory(CategoryRequest $request): array {
      return $this->repository->transaction(function () use ($request): array {
         try {
            // check
            $this->checkFields($request, false);

            // find image
            $category = $this->repository->findOne($request->id);
            $image = $category['image_path'] ?? null;

            // filter list
            $update = $request->filterArray([
               'code'       => $request->code,
               'title'      => $request->title,
               'content'    => $request->content,
               'image_path' => $request->image_path,
               'is_active'  => $request->is_active,
               'sort_order' => $request->sort_order
            ]);

            // update category
            $this->repository->update($update, [
               'id' => $request->id
            ]);

            // unlink old image
            if (isset($request->image_path)) {
               $this->unlink($image);
            }

            return $this->getOne($request->id);
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
   public function deleteCategory(int $id): bool {
      return $this->repository->transaction(function () use ($id): bool {
         // find image
         $image = $this->repository->findOne($id)['image_path'] ?? null;

         // delete category
         $this->repository->softDelete([
            'id'         => $id,
            'deleted_at' => ['IS NULL']
         ]);

         // unlink image
         if (isset($image)) {
            return $this->unlink($image);
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

         return $this->repository->findAll([
            'id' => ['IN', array_column($fields, 'id')]
         ]);
      });
   }

   /**
    * upload image
    */
   public function uploadImage(array $files): array {
      return $this->upload($files, 'category');
   }

   /**
    * delete image
    */
   public function deleteImage(int $id): bool {
      return $this->repository->transaction(function () use ($id): bool {
         // find image
         $image = $this->repository->findOne($id)['image_path'] ?? null;

         // update field to null
         $this->repository->update([
            'image_path' => null
         ], [
            'id' => $id
         ]);

         // unlink image
         if (isset($image)) {
            return $this->unlink($image);
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
