<?php

declare(strict_types=1);

namespace App\Modules\Category;

use System\Http\Request;
use System\Http\Response;
use App\Core\Abstracts\Controller;
use App\Modules\Category\CategoryRequest;
use App\Modules\Category\CategoryService;
use App\Modules\Category\CategoryResponse;

/**
 * @OA\Tag(name="Category", description="Kategori işlemleri")
 */
class CategoryController extends Controller {
   public function __construct(
      protected Response $response,
      protected Request $request,
      protected CategoryService $service
   ) {
   }

   /**
    * @OA\Get(
    *    tags={"Category"}, path="/category/", summary="Kategori listesi",
    *    @OA\Response(response=200, description="Success")
    * )
    */
   public function getAll(): void {
      $this->response(function () {
         $result = $this->service->getAll();

         return array_map(function ($item) {
            $response = new CategoryResponse();
            $response->withData($item);
            return $response;
         }, $result);
      });
   }

   /**
    * @OA\Get(tags={"Category"}, path="/category/{id}", summary="Kategori detayı (ID'ye göre)",
    *    @OA\Response(response=200, description="Success"),
    *    @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer"))
    * )
    */
   public function getById(int $id): void {
      $this->response(function () use ($id) {
         $result = $this->service->getOne($id);
         $response = new CategoryResponse();
         $response->withData($result);
         return $response;
      });
   }

   /**
    * @OA\Post(tags={"Category"}, path="/category/", summary="Kategori ekle",
    *    @OA\Response(response=201, description="Success"),
    *    @OA\RequestBody(required=true, @OA\JsonContent(
    *       required={"code", "title", "content", "image_path", "is_active", "sort_order"},
    *       @OA\Property(property="code", type="string", example="CAT001"),
    *       @OA\Property(property="title", type="string", example="Kategori Başlığı"),
    *       @OA\Property(property="content", type="string", example="Kategori Açıklaması"),
    *       @OA\Property(property="image_path", type="string", example="/images/categories/category1.jpg"),
    *       @OA\Property(property="is_active", type="integer", example=1),
    *       @OA\Property(property="sort_order", type="integer", example=1)
    *    ))
    * )
    */
   public function create(): void {
      $this->response(function () {
         $json = $this->request->json();
         $request = new CategoryRequest();
         $request->assignData($json);

         $result = $this->service->createCategory($request);
         $response = new CategoryResponse();
         $response->withData($result);
         return $response;
      }, code: 201);
   }

   /**
    * @OA\Put(tags={"Category"}, path="/category/", summary="Kategori güncelle",
    *    @OA\Response(response=200, description="Success"),
    *    @OA\RequestBody(required=true, @OA\JsonContent(
    *       required={"id", "code", "title", "content", "image_path", "is_active", "sort_order"},
    *       @OA\Property(property="id", type="integer", example=1),
    *       @OA\Property(property="code", type="string", example="CAT001"),
    *       @OA\Property(property="title", type="string", example="Kategori Başlığı"),
    *       @OA\Property(property="content", type="string", example="Kategori Açıklaması"),
    *       @OA\Property(property="image_path", type="string", example="/images/categories/category1.jpg"),
    *       @OA\Property(property="is_active", type="integer", example=1),
    *       @OA\Property(property="sort_order", type="integer", example=1)
    *    ))
    * )
    */
   public function update(): void {
      $this->response(function () {
         $json = $this->request->json();
         $request = new CategoryRequest();
         $request->assignData($json);

         $result = $this->service->updateCategory($request);
         $response = new CategoryResponse();
         $response->withData($result);
         return $response;
      });
   }

   /**
    * @OA\Delete(
    *    tags={"Category"}, path="/category/{id}", summary="Kategori sil",
    *    @OA\Response(response=200, description="Success"),
    *    @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer"))
    * )
    */
   public function delete(int $id): void {
      $this->response(function () use ($id) {
         return $this->service->deleteCategory($id);
      });
   }

   /**
    * @OA\Put(tags={"Category"}, path="/category/order", summary="Kategori sıralamasını toplu güncelle",
    *    @OA\Response(response=200, description="Success"),
    *    @OA\RequestBody(required=true, @OA\JsonContent(type="array",
    *       @OA\Items(required={"id", "order"},
    *          @OA\Property(property="id", type="integer", example=1),
    *          @OA\Property(property="order", type="integer", example=1)
    *       )
    *    ))
    * )
    */
   public function updateOrder(): void {
      $this->response(function () {
         $json = $this->request->json();
         $result = $this->service->updateOrder($json);

         return array_map(function ($item) {
            $response = new CategoryResponse();
            $response->withData($item);
            return $response;
         }, $result);
      });
   }

   /**
    * @OA\Post(tags={"Category"}, path="/category/image", summary="Kategori resmini yükle",
    *    @OA\Response(response=201, description="Success"),
    *    @OA\RequestBody(required=true, @OA\MediaType(mediaType="multipart/form-data",
    *       @OA\Schema(required={"files[]"},
    *          @OA\Property(property="files[]", type="array", @OA\Items(type="string", format="binary"))
    *       )
    *    ))
    * )
    */
   public function uploadImage(): void {
      $this->response(function () {
         $files = $this->request->files();
         return $this->service->uploadImage($files);
      });
   }

   /**
    * @OA\Delete(
    *    tags={"Category"}, path="/category/image/{id}", summary="Kategori resmini sil",
    *    @OA\Response(response=200, description="Success"),
    *    @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer"))
    * )
    */
   public function deleteImage(int $id): void {
      $this->response(function () use ($id) {
         return $this->service->deleteImage($id);
      });
   }
}
