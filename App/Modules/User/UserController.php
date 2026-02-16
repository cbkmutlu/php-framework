<?php

declare(strict_types=1);

namespace App\Modules\User;

use System\Http\{Request, Response};
use App\Core\Abstracts\Controller;
use App\Modules\User\{UserRequest, UserResponse, UserService};

/**
 * @OA\Tag(name="User", description="Kullanıcı yönetimi")
 */
class UserController extends Controller {
   public function __construct(
      protected Response $response,
      protected Request $request,
      protected UserService $service
   ) {
   }

   /**
    * @OA\Get(tags={"User"}, path="/user", summary="Tüm kullanıcıları listele",
    *    @OA\Response(response=200, description="Success")
    * )
    */
   public function getAll(): void {
      $result = $this->service->getAll();
      $list = array_map(function ($item) {
         $response = new UserResponse();
         return $response->withData($item);
      }, $result);

      $this->response->json($list);
   }

   /**
    * @OA\Get(tags={"User"}, path="/user/{id}", summary="Kullanıcı detayı (rolleri ve yetkileri ile)",
    *    @OA\Response(response=200, description="Success"),
    *    @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer"))
    * )
    */
   public function getById(int $id): void {
      $result = $this->service->getOne($id);
      $response = new UserResponse();
      $response->withData($result);

      $this->response->json($response);
   }

   /**
    * @OA\Post(tags={"User"}, path="/user", summary="Yeni kullanıcı oluştur",
    *    @OA\Response(response=201, description="Success"),
    *    @OA\RequestBody(required=true, @OA\JsonContent(
    *       required={"name", "surname", "email", "password", "status"},
    *       @OA\Property(property="name", type="string", example="John"),
    *       @OA\Property(property="surname", type="string", example="Doe"),
    *       @OA\Property(property="email", type="string", example="john@example.com"),
    *       @OA\Property(property="password", type="string", example="123456"),
    *       @OA\Property(property="status", type="integer", example=1),
    *       @OA\Property(property="roles", type="array", @OA\Items(type="object"))
    *    ))
    * )
    */
   public function create(): void {
      $json = $this->request->json();
      $request = new UserRequest();
      $request->assignData($json);

      $result = $this->service->createUser($request);
      $response = new UserResponse();
      $response->withData($result);

      $this->response->json($response, 201);
   }

   /**
    * @OA\Put(tags={"User"}, path="/user", summary="Kullanıcı güncelle",
    *    @OA\Response(response=200, description="Success"),
    *    @OA\RequestBody(required=true, @OA\JsonContent(
    *       required={"id", "name", "surname", "email", "status"},
    *       @OA\Property(property="id", type="integer", example=1),
    *       @OA\Property(property="name", type="string", example="John"),
    *       @OA\Property(property="surname", type="string", example="Doe"),
    *       @OA\Property(property="email", type="string", example="john@example.com"),
    *       @OA\Property(property="password", type="string", example="newpassword"),
    *       @OA\Property(property="status", type="integer", example=1),
    *       @OA\Property(property="roles", type="array", @OA\Items(type="object"))
    *    ))
    * )
    */
   public function update(): void {
      $json = $this->request->json();
      $request = new UserRequest();
      $request->assignData($json);

      $result = $this->service->updateUser($request);
      $response = new UserResponse();
      $response->withData($result);

      $this->response->json($response);
   }

   /**
    * @OA\Delete(tags={"User"}, path="/user/{id}", summary="Kullanıcı sil (soft delete)",
    *    @OA\Response(response=200, description="Success"),
    *    @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer"))
    * )
    */
   public function delete(int $id): void {
      $result = $this->service->deleteUser($id);

      $this->response->json($result);
   }

   /**
    * @OA\Put(tags={"User"}, path="/user/{id}/role", summary="Kullanıcı rollerini senkronize et",
    *    @OA\Response(response=200, description="Success"),
    *    @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
    *    @OA\RequestBody(required=true, @OA\JsonContent(
    *       required={"roles"},
    *       @OA\Property(property="roles", type="array", @OA\Items(type="object",
    *          @OA\Property(property="role_id", type="integer"),
    *          @OA\Property(property="scope_type", type="string"),
    *          @OA\Property(property="scope_id", type="integer")
    *       ))
    *    ))
    * )
    */
   public function syncRole(int $id): void {
      $json = $this->request->json();
      $roles = $json['roles'] ?? [];

      $result = $this->service->syncRole($id, $roles);
      $response = new UserResponse();
      $response->withData($result);

      $this->response->json($response);
   }

   /**
    * @OA\Post(tags={"User"}, path="/user/{id}/role/{roleId}", summary="Kullanıcıya rol ata",
    *    @OA\Response(response=200, description="Success"),
    *    @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
    *    @OA\Parameter(name="roleId", in="path", required=true, @OA\Schema(type="integer")),
    *    @OA\RequestBody(required=false, @OA\JsonContent(
    *       @OA\Property(property="scope_type", type="string", example="company"),
    *       @OA\Property(property="scope_id", type="integer", example=1)
    *    ))
    * )
    */
   public function giveRole(int $id, int $roleId): void {
      $json = $this->request->json();
      $scopeType = $json['scope_type'] ?? null;
      $scopeId = isset($json['scope_id']) ? (int) $json['scope_id'] : null;

      $result = $this->service->giveRole($id, $roleId, $scopeType, $scopeId);
      $response = new UserResponse();
      $response->withData($result);

      $this->response->json($response);
   }

   /**
    * @OA\Delete(tags={"User"}, path="/user/{id}/role/{roleId}", summary="Kullanıcıdan rol kaldır",
    *    @OA\Response(response=200, description="Success"),
    *    @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
    *    @OA\Parameter(name="roleId", in="path", required=true, @OA\Schema(type="integer"))
    * )
    */
   public function revokeRole(int $id, int $roleId): void {
      $json = $this->request->json();
      $scopeType = $json['scope_type'] ?? null;
      $scopeId = isset($json['scope_id']) ? (int) $json['scope_id'] : null;

      $result = $this->service->revokeRole($id, $roleId, $scopeType, $scopeId);
      $response = new UserResponse();
      $response->withData($result);

      $this->response->json($response);
   }

   /**
    * @OA\Put(tags={"User"}, path="/user/{id}/permission", summary="Kullanıcı yetkilerini senkronize et",
    *    @OA\Response(response=200, description="Success"),
    *    @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
    *    @OA\RequestBody(required=true, @OA\JsonContent(
    *       required={"permissions"},
    *       @OA\Property(property="permissions", type="array", @OA\Items(type="object",
    *          @OA\Property(property="permission_id", type="integer"),
    *          @OA\Property(property="type", type="string", enum={"allow", "deny"}),
    *          @OA\Property(property="scope_type", type="string"),
    *          @OA\Property(property="scope_id", type="integer")
    *       ))
    *    ))
    * )
    */
   public function syncPermission(int $id): void {
      $json = $this->request->json();
      $permissions = $json['permissions'] ?? [];

      $result = $this->service->syncPermission($id, $permissions);
      $response = new UserResponse();
      $response->withData($result);

      $this->response->json($response);
   }

   /**
    * @OA\Post(tags={"User"}, path="/user/{id}/permission/{permissionId}", summary="Kullanıcıya doğrudan yetki ver",
    *    @OA\Response(response=200, description="Success"),
    *    @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
    *    @OA\Parameter(name="permissionId", in="path", required=true, @OA\Schema(type="integer")),
    *    @OA\RequestBody(required=false, @OA\JsonContent(
    *       @OA\Property(property="type", type="string", enum={"allow", "deny"}, example="allow"),
    *       @OA\Property(property="scope_type", type="string", example="department"),
    *       @OA\Property(property="scope_id", type="integer", example=5)
    *    ))
    * )
    */
   public function givePermission(int $id, int $permissionId): void {
      $json = $this->request->json();
      $type = $json['type'] ?? 'allow';
      $scopeType = $json['scope_type'] ?? null;
      $scopeId = isset($json['scope_id']) ? (int) $json['scope_id'] : null;

      $result = $this->service->givePermission($id, $permissionId, $type, $scopeType, $scopeId);
      $response = new UserResponse();
      $response->withData($result);

      $this->response->json($response);
   }

   /**
    * @OA\Delete(tags={"User"}, path="/user/{id}/permission/{permissionId}", summary="Kullanıcıdan doğrudan yetkiyi kaldır",
    *    @OA\Response(response=200, description="Success"),
    *    @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
    *    @OA\Parameter(name="permissionId", in="path", required=true, @OA\Schema(type="integer"))
    * )
    */
   public function revokePermission(int $id, int $permissionId): void {
      $json = $this->request->json();
      $scopeType = $json['scope_type'] ?? null;
      $scopeId = isset($json['scope_id']) ? (int) $json['scope_id'] : null;

      $result = $this->service->revokePermission($id, $permissionId, $scopeType, $scopeId);
      $response = new UserResponse();
      $response->withData($result);

      $this->response->json($response);
   }
}
