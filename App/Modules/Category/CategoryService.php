<?php

declare(strict_types=1);

namespace App\Modules\Category;

use System\Database\Database;
use System\Validation\Validation;
use App\Core\Abstracts\BaseService;
use System\Exception\SystemException;
use App\Modules\Category\CategoryRequest;
use App\Modules\Category\CategoryRepository;

class CategoryService extends BaseService {
   /** @var CategoryRepository */
   protected mixed $repository;

   public function __construct(
      protected Database $database,
      protected Validation $validation,
      CategoryRepository $repository
   ) {
      $this->repository = $repository;
   }

   /**
    * create
    */
   public function createCategory(CategoryRequest $request): array {
      $this->checkFields($request);

      return $this->transaction(function () use ($request) {
         $id = $this->create([
            'code' => $request->code,
            'title' => $request->title,
            'content' => $request->content,
            'image_path' => $request->image_path,
            'is_active' => $request->is_active,
            'sort_order' => $request->sort_order
         ]);

         return $this->getOne($id);
      });
   }

   /**
    * update
    */
   public function updateCategory(CategoryRequest $request): array {
      $this->checkFields($request, false);

      return $this->transaction(function () use ($request) {
         // find image and unlink
         if ($request->image_path) {
            $this->unlink([
               'id' => $request->id,
               'table' => 'category',
               'field' => 'image_path'
            ]);
         }

         $this->update($request, [
            'code' => $request->code,
            'title' => $request->title,
            'content' => $request->content,
            'image_path' => $request->image_path,
            'is_active' => $request->is_active,
            'sort_order' => $request->sort_order
         ], [
            'id' => $request->id
         ]);

         return $this->getOne($request->id);
      });
   }

   /**
    * check
    */
   private function checkFields(CategoryRequest $request, bool $create = true): void {
      try {
         $this->validate($request->toArray(), [
            'code' => 'required',
            'title' => 'required',
            'is_active' => 'required|numeric',
            'sort_order' => 'required|numeric',
         ]);

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
