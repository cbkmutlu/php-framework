<?php

declare(strict_types=1);

namespace App\Modules\Brand;

use System\Exception\SystemException;
use App\Core\Abstracts\Service;
use App\Modules\Brand\{BrandRepository, BrandRequest};

class BrandService extends Service {
    public function __construct(
        protected BrandRepository $repository
    ) {
    }

    /**
     * Return all brands
     */
    public function getAll(): array {
        return $this->repository->findAll();
    }

    /**
     * Return brand by id
     */
    public function getOne(int $brandId): array {
        $result = $this->repository->findOne($brandId);
        if ($result === null) {
            throw new SystemException('Record not found', 404);
        }

        return $result;
    }

    /**
     * Create brand
     */
    public function createBrand(BrandRequest $request): array {
        return $this->repository->transaction(function () use ($request): array {
            // check fields
            $this->checkFields($request);

            // create brand
            $create = $this->repository->create([
                'title'      => $request->title,
                'content'    => $request->content,
                'is_active'  => $request->is_active,
                'sort_order' => $request->sort_order
            ]);
            $brandId = $create->lastInsertId();

            // return created brand
            return $this->getOne($brandId);
        });
    }

    /**
     * Update brand
     */
    public function updateBrand(BrandRequest $request): array {
        return $this->repository->transaction(function () use ($request): array {
            // check fields
            $this->checkFields($request, false);

            // filter only request fields
            $update = $request->filter([
                'title'      => $request->title,
                'content'    => $request->content,
                'is_active'  => $request->is_active,
                'sort_order' => $request->sort_order
            ]);

            // update brand
            $this->repository->update($update, [
                'id' => $request->id
            ]);

            // return updated brand
            return $this->getOne($request->id);
        });
    }

    /**
     * Delete brand (soft delete)
     */
    public function deleteBrand(int $brandId): bool {
        return $this->repository->transaction(function () use ($brandId): bool {
            // check relation
            if ($this->repository->hasProductRelation($brandId)) {
                throw new SystemException('Brand has associated products', 400);
            }

            // delete brand
            $this->repository->softDelete([
                'id'         => $brandId,
                'deleted_at' => ['IS NULL']
            ]);

            return true;
        });
    }

    /**
     * Check fields
     */
    private function checkFields(BrandRequest $request, bool $create = true): void {
        $this->check($this->repository, [
            'title' => $request->title
        ], $request, $create);
    }
}
