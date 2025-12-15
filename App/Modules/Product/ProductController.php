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
    *    tags={"Product"}, path="/product/", summary="Ürün listesi",
    *    @OA\Response(response=200, description="Success")
    * )
    */
   public function getAll() {
      $this->response(function () {
         $result = $this->service->getAll();

         return array_map(function ($item) {
            $response = new ProductResponse();
            $response->withData($item);
            return $response;
         }, $result);
      });
   }

   /**
    * @OA\Get(tags={"Product"}, path="/product/{id}", summary="Ürün detayı (ID'ye göre)",
    *    @OA\Response(response=200, description="Success"),
    *    @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer"))
    * )
    */
   public function getById(int $id) {
      $this->response(function () use ($id) {
         $result = $this->service->getOne($id);
         $response = new ProductResponse();
         $response->withData($result);
         return $response;
      });
   }

   /**
    * @OA\Post(tags={"Product"}, path="/product/", summary="Ürün ekle",
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
      $this->response(function () {
         $json = $this->request->json();
         $request = new ProductRequest();
         $request->assignData($json);

         $result = $this->service->createProduct($request);
         $response = new ProductResponse();
         $response->withData($result);
         return $response;
      }, code: 201);
   }

   /**
    * @OA\Put(tags={"Product"}, path="/product/", summary="Ürün güncelle",
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
      $this->response(function () {
         $json = $this->request->json();
         $request = new ProductRequest();
         $request->assignData($json);

         $result = $this->service->updateProduct($request);
         $response = new ProductResponse();
         $response->withData($result);
         return $response;
      });
   }

   /**
    * @OA\Delete(
    *    tags={"Product"}, path="/product/{id}", summary="Ürün sil",
    *    @OA\Response(response=200, description="Success"),
    *    @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer"))
    * )
    */
   public function delete(int $id): void {
      $this->response(function () use ($id) {
         return $this->service->deleteProduct($id);
      });
   }
}
