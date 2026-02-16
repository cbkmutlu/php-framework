<?php

declare(strict_types=1);

namespace App\Modules\Permission;

use System\Http\{Request, Response};
use App\Core\Abstracts\Controller;
use App\Modules\Permission\{PermissionRequest, PermissionResponse, PermissionService};

/**
 * @OA\Tag(name="Permission", description="Permission yönetimi")
 */
class PermissionController extends Controller {
   public function __construct(
      protected Response $response,
      protected Request $request,
      protected PermissionService $service
   ) {
   }

   /**
    * @OA\Get(tags={"Permission"}, path="/permission", summary="Tüm yetkileri listele",
    *    @OA\Response(response=200, description="Success")
    * )
    */
   public function getAll(): void {
      $result = $this->service->getAll();
      $list = array_map(function ($item) {
         $response = new PermissionResponse();
         return $response->withData($item);
      }, $result);

      $this->response->json($list);
   }

   /**
    * @OA\Get(tags={"Permission"}, path="/permission/{id}", summary="Yetki detayı",
    *    @OA\Response(response=200, description="Success"),
    *    @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer"))
    * )
    */
   public function getById(int $id): void {
      $result = $this->service->getOne($id);
      $response = new PermissionResponse();
      $response->withData($result);

      $this->response->json($response);
   }

   /**
    * @OA\Post(tags={"Permission"}, path="/permission", summary="Yeni yetki oluştur",
    *    @OA\Response(response=201, description="Success"),
    *    @OA\RequestBody(required=true, @OA\JsonContent(
    *       required={"name", "slug"},
    *       @OA\Property(property="name", type="string", example="View Dashboard"),
    *       @OA\Property(property="slug", type="string", example="dashboard.view"),
    *       @OA\Property(property="group_name", type="string", example="Dashboard"),
    *       @OA\Property(property="description", type="string", example="View dashboard page")
    *    ))
    * )
    */
   public function create(): void {
      $json = $this->request->json();
      $request = new PermissionRequest();
      $request->assignData($json);

      $result = $this->service->createPermission($request);
      $response = new PermissionResponse();
      $response->withData($result);

      $this->response->json($response, 201);
   }

   /**
    * @OA\Put(tags={"Permission"}, path="/permission", summary="Yetkiyi güncelle",
    *    @OA\Response(response=200, description="Success"),
    *    @OA\RequestBody(required=true, @OA\JsonContent(
    *       required={"id", "name", "slug"},
    *       @OA\Property(property="id", type="integer", example=1),
    *       @OA\Property(property="name", type="string", example="View Dashboard"),
    *       @OA\Property(property="slug", type="string", example="dashboard.view"),
    *       @OA\Property(property="group_name", type="string", example="Dashboard"),
    *       @OA\Property(property="description", type="string", example="Updated description")
    *    ))
    * )
    */
   public function update(): void {
      $json = $this->request->json();
      $request = new PermissionRequest();
      $request->assignData($json);

      $result = $this->service->updatePermission($request);
      $response = new PermissionResponse();
      $response->withData($result);

      $this->response->json($response);
   }

   /**
    * @OA\Delete(tags={"Permission"}, path="/permission/{id}", summary="Yetkiyi sil (hard delete)",
    *    @OA\Response(response=200, description="Success"),
    *    @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer"))
    * )
    */
   public function delete(int $id): void {
      $result = $this->service->deletePermission($id);

      $this->response->json($result);
   }

   /**
    * @OA\Get(tags={"Permission"}, path="/permission/grouped", summary="Gruplara göre yetkileri getir",
    *    @OA\Response(response=200, description="Success")
    * )
    */
   public function getGrouped(): void {
      $result = $this->service->getGrouped();

      $this->response->json($result);
   }
}
