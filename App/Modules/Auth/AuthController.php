<?php

declare(strict_types=1);

namespace App\Modules\Auth;

use System\Http\Request;
use System\Http\Response;
use App\Modules\Auth\AuthService;
use App\Core\Abstracts\Controller;

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
      $this->response(function () {
         $json = $this->request->json();
         $this->service->validate($json, [
            'email'    => ['required', 'email'],
            'password' => ['required', 'string']
         ]);

         $result = $this->service->login($json['email'], $json['password']);
         return $result;
      });
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
      $this->response(function () {
         $json = $this->request->json();
         $this->service->validate($json, [
            'token' => ['required', 'string']
         ]);

         $this->service->logout($json['token']);
         return true;
      });
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
      $this->response(function () {
         $json = $this->request->json();
         $this->service->validate($json, [
            'user_id' => ['required', 'integer']
         ]);

         $this->service->logoutAll($json['user_id']);
         return true;
      });
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
      $this->response(function () {
         $json = $this->request->json();
         $this->service->validate($json, [
            'token' => ['required', 'string']
         ]);

         $result = $this->service->refresh($json['token']);
         return $result;
      });
   }
}
