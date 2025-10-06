<?php

declare(strict_types=1);

namespace App\Modules\Category;

use System\Database\Database;
use System\Validation\Validation;
use App\Core\Abstracts\BaseService;
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

   public function createCategory(CategoryRequest $dto): array {
      return $this->transaction(function () use ($dto) {
         $this->validate($dto->toArray(), [
            'code' => 'required',
            'title' => 'required',
            'image_path' => 'nullable',
            'is_active' => 'required|numeric',
            'sort_order' => 'required|numeric'
         ]);

         $id = $this->create([
            'code' => $dto->code,
            'title' => $dto->title,
            'content' => $dto->content,
            'image_path' => $dto->image_path,
            'is_active' => $dto->is_active,
            'sort_order' => $dto->sort_order
         ]);

         return $this->getOne($id);
      });
   }

   public function updateCategory(CategoryRequest $dto): array {
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
            'image_path' => $dto->image_path,
            'is_active' => $dto->is_active,
            'sort_order' => $dto->sort_order
         ], [
            'id' => $dto->id
         ]);

         return $this->getOne($dto->id);
      });
   }
}
