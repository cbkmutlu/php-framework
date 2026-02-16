<?php

declare(strict_types=1);

namespace App\Modules\Auth;

use System\Http\{Request, Response};
use App\Core\Abstracts\Controller;
use App\Modules\Auth\AuthService;

/**
 * @OA\Tag(name="Auth", description="Auth işlemleri")
 */
class AuthController extends Controller {
   public function __construct(
      protected Response $response,
      protected Request $request,
      protected AuthService $service
   ) {
   }

   /**
    * @OA\Post(tags={"Auth"}, path="/auth/login", summary="Giriş yap",
    *    @OA\Response(response=201, description="Success"),
    *    @OA\RequestBody(required=true, @OA\JsonContent(
    *       required={"email", "password"},
    *       @OA\Property(property="email", type="string", example="admin@example.com"),
    *       @OA\Property(property="password", type="string", example="admin")
    *    ))
    * )
    */
   public function login(): void {
      $json = $this->request->json();
      $this->service->validate($json, [
         'email'    => ['required', 'email'],
         'password' => ['required', 'string']
      ]);

      $result = $this->service->login($json['email'], $json['password']);
      $this->response->json($result);
   }

   /**
    * @OA\Post(tags={"Auth"}, path="/auth/logout", summary="Çıkış yap",
    *    @OA\Response(response=201, description="Success"),
    *    @OA\RequestBody(required=true, @OA\JsonContent(
    *       required={"token"},
    *       @OA\Property(property="token", type="string", example="access_token")
    *    ))
    * )
    */
   public function logout(): void {
      $json = $this->request->json();
      $this->service->validate($json, [
         'token' => ['required', 'string']
      ]);

      $result = $this->service->logout($json['token']);
      $this->response->json($result);
   }

   /**
    * @OA\Post(tags={"Auth"}, path="/auth/logoutall", summary="Tüm cihazlardan çıkış yap",
    *    @OA\Response(response=201, description="Success"),
    *    @OA\RequestBody(required=true, @OA\JsonContent(
    *       required={"user_id"},
    *       @OA\Property(property="user_id", type="integer", example=1)
    *    ))
    * )
    */
   public function logoutAll(): void {
      $json = $this->request->json();
      $this->service->validate($json, [
         'user_id' => ['required', 'integer']
      ]);

      $result = $this->service->logoutAll($json['user_id']);
      $this->response->json($result);
   }

   /**
    * @OA\Post(tags={"Auth"}, path="/auth/refresh", summary="Yenileme",
    *    @OA\Response(response=201, description="Success"),
    *    @OA\RequestBody(required=true, @OA\JsonContent(
    *       required={"token"},
    *       @OA\Property(property="token", type="string", example="refresh_token")
    *    ))
    * )
    */
   public function refresh(): void {
      $json = $this->request->json();
      $this->service->validate($json, [
         'token' => ['required', 'string']
      ]);

      $result = $this->service->refresh($json['token']);
      $this->response->json($result);
   }
}
