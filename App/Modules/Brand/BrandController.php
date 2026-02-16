<?php

declare(strict_types=1);

namespace App\Modules\Brand;

use System\Http\{Request, Response};
use App\Core\Abstracts\Controller;
use App\Modules\Brand\{BrandRequest, BrandResponse, BrandService};

/**
 * @OA\Tag(name="Brand", description="Marka işlemleri")
 */
class BrandController extends Controller {
   public function __construct(
      protected Response $response,
      protected Request $request,
      protected BrandService $service
   ) {
   }

   /**
    * @OA\Get(tags={"Brand"}, path="/brand", summary="Marka listesi",
    *    @OA\Response(response=200, description="Success")
    * )
    */
   public function getAll(): void {
      $result = $this->service->getAll();
      $list = array_map(function ($item) {
         $response = new BrandResponse();
         return $response->withData($item);
      }, $result);

      $this->response->json($list);
   }

   /**
    * @OA\Get(tags={"Brand"}, path="/brand/{id}", summary="Marka detayı (ID'ye göre)",
    *    @OA\Response(response=200, description="Success"),
    *    @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer"))
    * )
    */
   public function getById(int $brandId): void {
      $result = $this->service->getOne($brandId);
      $response = new BrandResponse();
      $response->withData($result);

      $this->response->json($response);
   }

   /**
    * @OA\Post(tags={"Brand"}, path="/brand", summary="Marka ekle",
    *    @OA\Response(response=201, description="Success"),
    *    @OA\RequestBody(required=true, @OA\JsonContent(
    *       required={"title"},
    *       @OA\Property(property="title", type="string", example="Marka Adı"),
    *       @OA\Property(property="content", type="string", example="Marka Açıklaması"),
    *       @OA\Property(property="is_active", type="integer", example=1),
    *       @OA\Property(property="sort_order", type="integer", example=1)
    *    ))
    * )
    */
   public function create(): void {
      $json = $this->request->json();
      $request = new BrandRequest();
      $request->assignData($json);

      $result = $this->service->createBrand($request);
      $response = new BrandResponse();
      $response->withData($result);

      $this->response->json($response, 201);
   }

   /**
    * @OA\Put(tags={"Brand"}, path="/brand", summary="Marka güncelle",
    *    @OA\Response(response=200, description="Success"),
    *    @OA\RequestBody(required=true, @OA\JsonContent(
    *       required={"id", "title"},
    *       @OA\Property(property="id", type="integer", example=1),
    *       @OA\Property(property="title", type="string", example="Marka Adı"),
    *       @OA\Property(property="content", type="string", example="Marka Açıklaması"),
    *       @OA\Property(property="is_active", type="integer", example=1),
    *       @OA\Property(property="sort_order", type="integer", example=1)
    *    ))
    * )
    */
   public function update(): void {
      $json = $this->request->json();
      $request = new BrandRequest();
      $request->assignData($json);

      $result = $this->service->updateBrand($request);
      $response = new BrandResponse();
      $response->withData($result);

      $this->response->json($response);
   }

   /**
    * @OA\Delete(tags={"Brand"}, path="/brand/{id}", summary="Marka sil",
    *    @OA\Response(response=200, description="Success"),
    *    @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer"))
    * )
    */
   public function delete(int $brandId): void {
      $result = $this->service->deleteBrand($brandId);

      $this->response->json($result);
   }
}
