<?php

declare(strict_types=1);

namespace App\Modules\Role;

use System\Gate\Gate;
use System\Http\{Request, Response};
use App\Core\Abstracts\Controller;
use App\Modules\Role\{RolePolicy, RoleRequest, RoleResponse, RoleService};

/**
 * @OA\Tag(name="Role", description="Rol yönetimi")
 */
class RoleController extends Controller {
   public function __construct(
      protected Response $response,
      protected Request $request,
      protected RoleService $service,
      protected Gate $gate,
      protected RolePolicy $policy
   ) {
   }

   /**
    * @OA\Get(tags={"Role"}, path="/role", summary="Tüm rolleri listele",
    *    @OA\Response(response=200, description="Success")
    * )
    */
   public function getAll(): void {
      $this->gate->authorize($this->policy, 'viewAny');

      $result = $this->service->getAll();
      $list = array_map(function ($item) {
         $response = new RoleResponse();
         return $response->withData($item);
      }, $result);

      $this->response->json($list);
   }

   /**
    * @OA\Get(tags={"Role"}, path="/role/{id}", summary="Rol detayı (yetkileri ile birlikte)",
    *    @OA\Response(response=200, description="Success"),
    *    @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer"))
    * )
    */
   public function getById(int $id): void {
      $this->gate->authorize($this->policy, 'view');

      $result = $this->service->getOne($id);
      $response = new RoleResponse();
      $response->withData($result);

      $this->response->json($response);
   }

   /**
    * @OA\Post(tags={"Role"}, path="/role", summary="Yeni rol oluştur",
    *    @OA\Response(response=201, description="Success"),
    *    @OA\RequestBody(required=true, @OA\JsonContent(
    *       required={"name", "slug"},
    *       @OA\Property(property="name", type="string", example="Editor"),
    *       @OA\Property(property="slug", type="string", example="editor"),
    *       @OA\Property(property="description", type="string", example="İçerik editörü"),
    *       @OA\Property(property="permissions", type="array", @OA\Items(type="integer"), example={1, 2, 3})
    *    ))
    * )
    */
   public function create(): void {
      $this->gate->authorize($this->policy, 'create');

      $json = $this->request->json();
      $request = new RoleRequest();
      $request->assignData($json);

      $result = $this->service->createRole($request);
      $response = new RoleResponse();
      $response->withData($result);

      $this->response->json($response, 201);
   }

   /**
    * @OA\Put(tags={"Role"}, path="/role", summary="Rol güncelle",
    *    @OA\Response(response=200, description="Success"),
    *    @OA\RequestBody(required=true, @OA\JsonContent(
    *       required={"id", "name", "slug"},
    *       @OA\Property(property="id", type="integer", example=1),
    *       @OA\Property(property="name", type="string", example="Editor"),
    *       @OA\Property(property="slug", type="string", example="editor"),
    *       @OA\Property(property="description", type="string", example="Güncellenmiş açıklama"),
    *       @OA\Property(property="permissions", type="array", @OA\Items(type="integer"), example={1, 2, 3})
    *    ))
    * )
    */
   public function update(): void {
      $this->gate->authorize($this->policy, 'update');

      $json = $this->request->json();
      $request = new RoleRequest();
      $request->assignData($json);

      $result = $this->service->updateRole($request);
      $response = new RoleResponse();
      $response->withData($result);

      $this->response->json($response);
   }

   /**
    * @OA\Delete(tags={"Role"}, path="/role/{id}", summary="Rol sil (hard delete)",
    *    @OA\Response(response=200, description="Success"),
    *    @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer"))
    * )
    */
   public function delete(int $id): void {
      $this->gate->authorize($this->policy, 'delete');

      $result = $this->service->deleteRole($id);

      $this->response->json($result);
   }

   /**
    * @OA\Put(tags={"Role"}, path="/role/{id}/permission", summary="Rol yetkilerini senkronize et",
    *    @OA\Response(response=200, description="Success"),
    *    @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
    *    @OA\RequestBody(required=true, @OA\JsonContent(
    *       required={"permissions"},
    *       @OA\Property(property="permissions", type="array", @OA\Items(type="integer"), example={1, 2, 5, 8})
    *    ))
    * )
    */
   public function syncPermission(int $id): void {
      $json = $this->request->json();
      $permissionIds = $json['permissions'] ?? [];

      $result = $this->service->syncPermission($id, $permissionIds);
      $response = new RoleResponse();
      $response->withData($result);

      $this->response->json($response);
   }

   /**
    * @OA\Post(tags={"Role"}, path="/role/{id}/permission/{permissionId}", summary="Role yetki ekle",
    *    @OA\Response(response=200, description="Success"),
    *    @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
    *    @OA\Parameter(name="permissionId", in="path", required=true, @OA\Schema(type="integer"))
    * )
    */
   public function givePermission(int $id, int $permissionId): void {
      $result = $this->service->givePermission($id, $permissionId);
      $response = new RoleResponse();
      $response->withData($result);

      $this->response->json($response);
   }

   /**
    * @OA\Delete(tags={"Role"}, path="/role/{id}/permission/{permissionId}", summary="Rolden yetki kaldır",
    *    @OA\Response(response=200, description="Success"),
    *    @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
    *    @OA\Parameter(name="permissionId", in="path", required=true, @OA\Schema(type="integer"))
    * )
    */
   public function revokePermission(int $id, int $permissionId): void {
      $result = $this->service->revokePermission($id, $permissionId);
      $response = new RoleResponse();
      $response->withData($result);

      $this->response->json($response);
   }
}
