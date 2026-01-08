<?php

declare(strict_types=1);

namespace App\Modules\Product;

use System\Http\Request;
use System\Http\Response;
use App\Core\Abstracts\Controller;
use App\Modules\Product\ProductRequest;
use App\Modules\Product\ProductService;
use App\Modules\Product\ProductResponse;

/**
 * @OA\Tag(name="Product", description="Ürün işlemleri")
 */
class ProductController extends Controller {
   public function __construct(
      protected Response $response,
      protected Request $request,
      protected ProductService $service
   ) {
   }

   /**
    * @OA\Get(
    *    tags={"Product"}, path="/product", summary="Ürün listesi",
    *    @OA\Response(response=200, description="Success")
    * )
    */
   public function getAll() {
      $result = $this->service->getAll();
      $list = array_map(function ($item) {
         $response = new ProductResponse();
         return $response->withData($item);
      }, $result);

      $this->response->json($list);
   }

   /**
    * @OA\Get(tags={"Product"}, path="/product/{id}", summary="Ürün detayı (id'ye göre)",
    *    @OA\Response(response=200, description="Success"),
    *    @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer"))
    * )
    */
   public function getById(int $productId) {
      $result = $this->service->getOne($productId);
      $response = new ProductResponse();
      $response->withData($result);

      $this->response->json($response);
   }

   /**
    * @OA\Post(tags={"Product"}, path="/product", summary="Ürün ekle",
    *    @OA\Response(response=201, description="Success"),
    *    @OA\RequestBody(required=true, @OA\JsonContent(
    *       required={"code", "title", "content", "is_active", "sort_order"},
    *       @OA\Property(property="code", type="string", example="PRD001"),
    *       @OA\Property(property="title", type="string", example="Koruyucu Eldiven"),
    *       @OA\Property(property="content", type="string", example="Yüksek kaliteli koruyucu eldiven"),
    *       @OA\Property(property="is_active", type="integer", example=1),
    *       @OA\Property(property="sort_order", type="integer", example=1),
    *       @OA\Property(property="product_category", type="array", @OA\Items(type="integer"), example={1, 2})
    *    ))
    * )
    */
   public function create() {
      $json = $this->request->json();
      $request = new ProductRequest();
      $request->assignData($json);

      $result = $this->service->createProduct($request);
      $response = new ProductResponse();
      $response->withData($result);

      $this->response->json($response, 201);
   }

   /**
    * @OA\Put(tags={"Product"}, path="/product", summary="Ürün güncelle",
    *    @OA\Response(response=200, description="Success"),
    *    @OA\RequestBody(required=true, @OA\JsonContent(
    *       required={"id", "code", "title", "content", "is_active", "sort_order"},
    *       @OA\Property(property="id", type="integer", example=1),
    *       @OA\Property(property="code", type="string", example="PRD001"),
    *       @OA\Property(property="title", type="string", example="Koruyucu Eldiven"),
    *       @OA\Property(property="content", type="string", example="Yüksek kaliteli koruyucu eldiven"),
    *       @OA\Property(property="is_active", type="integer", example=1),
    *       @OA\Property(property="sort_order", type="integer", example=1),
    *       @OA\Property(property="product_category", type="array", @OA\Items(type="integer"), example={1, 2})
    *    ))
    * )
    */
   public function update() {
      $json = $this->request->json();
      $request = new ProductRequest();
      $request->assignData($json);

      $result = $this->service->updateProduct($request);
      $response = new ProductResponse();
      $response->withData($result);

      $this->response->json($response);
   }

   /**
    * @OA\Delete(
    *    tags={"Product"}, path="/product/{id}", summary="Ürün sil",
    *    @OA\Response(response=200, description="Success"),
    *    @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer"))
    * )
    */
   public function delete(int $id): void {
      $result = $this->service->deleteProduct($id);

      $this->response->json($result);
   }

   /**
    * @OA\Post(tags={"Product"}, path="/product/image", summary="Ürün resimlerini yükle (çoklu resim)",
    *    @OA\Response(response=201, description="Success"),
    *    @OA\RequestBody(required=true, @OA\MediaType(mediaType="multipart/form-data",
    *       @OA\Schema(required={"files[]"},
    *          @OA\Property(property="files[]", type="array", @OA\Items(type="string", format="binary"))
    *       )
    *    ))
    * )
    */
   public function uploadImage(): void {
      $files = $this->request->files('files');
      $result = $this->service->uploadImage($files);

      $this->response->json($result);
   }

   /**
    * @OA\Delete(
    *    tags={"Product"}, path="/product/image/{image_id}", summary="Ürün resmini sil (image_id'ye göre)",
    *    @OA\Response(response=200, description="Success"),
    *    @OA\Parameter(name="image_id", in="path", required=true, @OA\Schema(type="integer"))
    * )
    */
   public function deleteImage(int $imageId): void {
      $result = $this->service->deleteImage($imageId);

      $this->response->json($result);
   }
}
