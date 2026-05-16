<?php

declare(strict_types=1);

namespace App\Modules\Product;

use System\Date\Date;
use System\Exception\SystemException;
use System\Upload\Upload;
use App\Core\Abstracts\Service;
use App\Modules\Product\{ProductRepository, ProductRequest};

class ProductService extends Service {
    public function __construct(
        protected Upload $upload,
        protected Date $date,
        protected ProductRepository $repository
    ) {
    }

    /**
     * Return all products
     */
    public function getAll(): array {
        $result = $this->repository->findAll();

        return array_map(function ($product) {
            $product = $this->getRelation($product);
            return $product;
        }, $result);
    }

    /**
     * Return product by id
     */
    public function getOne(int $productId): array {
        $result = $this->repository->findOne($productId);

        if (empty($result)) {
            throw new SystemException('Record not found', 404);
        }

        return $this->getRelation($result);
    }

    /**
     * Create product
     */
    public function createProduct(ProductRequest $request): array {
        return $this->repository->transaction(function () use ($request): array {
            try {
                // check fields
                $this->checkFields($request);

                // create product
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
                $productId = $create->lastInsertId();

                // create relation
                $product = $this->repository->findOne($productId);
                $this->createRelation($product, $request);

                // return created product with relation
                return $this->getRelation($product);
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
     * Update product
     */
    public function updateProduct(ProductRequest $request): array {
        return $this->repository->transaction(function () use ($request): array {
            try {
                // check fields
                $this->checkFields($request, false);

                // get product
                $product = $this->getOne($request->id);

                // filter only request properties
                $update = $request->filter([
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

                // sync relation
                $this->syncRelation($product, $request);

                // return updated product with relation (from getOne method)
                return $this->getOne($request->id);
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
     * Delete product
     */
    public function deleteProduct(int $productId): bool {
        return $this->repository->transaction(function () use ($productId): bool {
            // get product and find image relation
            $product = $this->getOne($productId);
            $productImage = array_values(array_column($product['image_list'] ?? [], 'image_path'));

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
            if (isset($productImage)) {
                return $this->unlink($productImage);
            }

            return true;
        });
    }

    /**
     * Upload product image
     */
    public function uploadImage(?array $files): array {
        return $this->upload($files, 'product');
    }

    /**
     * Delete product image (unlink + delete record)
     */
    public function deleteImage(int $imageId): bool {
        return $this->repository->transaction(function () use ($imageId): bool {
            // get image
            $productImage = $this->repository->findImagePath($imageId);

            // delete image
            $this->repository->hardDelete([
                'id' => $imageId
            ], 'product_image');

            // unlink image
            if (isset($productImage)) {
                return $this->unlink($productImage);
            }

            return true;
        });
    }

    /**
     * Get relation
     */
    private function getRelation(array $product): array {
        $productId = $product['id'];
        $brandId = $product['brand_id'];
        $product['category_list'] = $this->repository->findAllCategory($productId);
        $product['image_list'] = $this->repository->findAllImage($productId);
        $product['brand'] = $this->repository->findBrand($brandId);
        return $product;
    }

    /**
     * Sync relation
     */
    private function syncRelation(array $product, ProductRequest $request): void {
        $this->deleteRelation($product, $request);
        $this->createRelation($product, $request);
    }

    /**
     * Create relation
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
     * Delete relation
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
     * Check fields
     */
    private function checkFields(ProductRequest $request, bool $create = true): void {
        $this->check($this->repository, [
            'code' => $request->code
        ], $request, $create);
    }
}
