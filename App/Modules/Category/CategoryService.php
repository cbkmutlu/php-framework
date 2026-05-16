<?php

declare(strict_types=1);

namespace App\Modules\Category;

use System\Exception\SystemException;
use System\Upload\Upload;
use System\Validation\Validation;
use App\Core\Abstracts\Service;
use App\Modules\Category\{CategoryRepository, CategoryRequest};

class CategoryService extends Service {
    public function __construct(
        protected Upload $upload,
        protected Validation $validation,
        protected CategoryRepository $repository
    ) {
    }

    /**
     * Return all categories
     */
    public function getAll(int $langId = 1): array {
        return $this->repository->findAllWithTranslate($langId);
    }

    /**
     * Return category by id
     */
    public function getOne(int $categoryId, int $langId = 1): array {
        $result = $this->repository->findOneWithTranslate($categoryId, $langId);

        if (empty($result)) {
            throw new SystemException('Record not found', 404);
        }

        return $result;
    }

    /**
     * Create category
     */
    public function createCategory(CategoryRequest $request, int $langId = 1): array {
        return $this->repository->transaction(function () use ($request, $langId): array {
            try {
                // check fields
                $this->checkFields($request);

                // create category
                $create = $this->repository->create([
                    'code'       => $request->code,
                    'is_active'  => $request->is_active,
                    'sort_order' => $request->sort_order,
                    'image_path' => $request->image_path
                ]);
                $categoryId = $create->lastInsertId();

                // translate
                $this->translate($this->repository, ['title', 'content'], [
                    'category_id' => $categoryId
                ], $request->translate, 'category_translate');

                // return created category
                return $this->getOne($categoryId, $langId);
            } catch (SystemException $e) {
                // unlink uploaded image
                if (isset($request->image_path)) {
                    $this->unlink($request->image_path);
                }

                throw $e;
            }
        });
    }

    /**
     * Update category
     */
    public function updateCategory(CategoryRequest $request, int $langId = 1): array {
        return $this->repository->transaction(function () use ($request, $langId): array {
            try {
                // check fields
                $this->checkFields($request, false);

                // find image
                $categoryImage = $this->repository->findImagePath($request->id);

                // filter only request fields
                $update = $request->filter([
                    'code'       => $request->code,
                    'is_active'  => $request->is_active,
                    'sort_order' => $request->sort_order,
                    'image_path' => $request->image_path
                ]);

                // update category
                $this->repository->update($update, [
                    'id' => $request->id
                ]);

                // translate
                $this->translate($this->repository, ['title', 'content'], [
                    'category_id' => $request->id
                ], $request->translate, 'category_translate');

                // unlink existing image
                if (isset($request->image_path)) {
                    $this->unlink($categoryImage);
                }

                // return updated category
                return $this->getOne($request->id, $langId);
            } catch (SystemException $e) {
                // unlink uploaded image
                if (isset($request->image_path)) {
                    $this->unlink($request->image_path);
                }

                throw $e;
            }
        });
    }

    /**
     * Delete category (soft delete)
     */
    public function deleteCategory(int $categoryId): bool {
        return $this->repository->transaction(function () use ($categoryId): bool {
            // get image
            $categoryImage = $this->repository->findImagePath($categoryId);

            // check relation
            if ($this->repository->hasProductRelation($categoryId)) {
                throw new SystemException('Category has associated products', 400);
            }

            // delete category
            $this->repository->softDelete([
                'id'         => $categoryId,
                'deleted_at' => ['IS NULL']
            ]);

            // unlink image
            if (isset($categoryImage)) {
                return $this->unlink($categoryImage);
            }

            return true;
        });
    }

    /**
     * Update category order (bulk)
     */
    public function updateOrder(array $fields): array {
        $this->validate($fields, [
            '*.id'         => ['required', 'integer'],
            '*.sort_order' => ['required', 'integer']
        ], [
            '*.id'         => 'Kategori ID',
            '*.sort_order' => 'Sıralama'
        ]);

        return $this->repository->transaction(function () use ($fields): array {
            // fields
            $fields = array_map(function ($item) {
                return [
                    'id'         => $item['id'],
                    'sort_order' => $item['sort_order']
                ];
            }, $fields);

            // update category order (bulk)
            $columns = implode(',', array_column($fields, 'id'));
            $this->repository->update([
                'sort_order' => ['CASE', 'id', $fields]
            ], [
                'id' => ['IN', $columns]
            ]);

            // return updated categories
            return $this->repository->findUpdated($columns);
        });
    }

    /**
     * Upload category image
     */
    public function uploadImage(?array $files): array {
        return $this->upload($files, 'category');
    }

    /**
     * Delete category image (unlink + update image field)
     */
    public function deleteImage(int $categoryId): bool {
        return $this->repository->transaction(function () use ($categoryId): bool {
            // get image
            $categoryImage = $this->repository->findImagePath($categoryId);

            // update category (image_path = null)
            $this->repository->update([
                'image_path' => null
            ], [
                'id' => $categoryId
            ]);

            // unlink image
            if (isset($categoryImage)) {
                return $this->unlink($categoryImage);
            }

            return true;
        });
    }

    /**
     * Check fields
     */
    private function checkFields(CategoryRequest $request, bool $create = true): void {
        $this->check($this->repository, [
            'code' => $request->code
        ], $request, $create);
    }
}
