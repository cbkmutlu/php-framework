<?php

declare(strict_types=1);

namespace App\Modules\User\Controllers;

use System\Controller\Controller;
use System\Http\Request;
use App\Modules\User\Models\LoginModel;
use System\Http\Response;
use System\Jwt\Jwt;

/**
 * @OA\Tag(name="User", description="User işlemleri")
 */
class LoginController extends Controller {

   public function __construct(
      private LoginModel $model,
      private Request $request,
      private Response $response,
      private Jwt $jwt
   ) {
   }

   /**
    * @OA\Post(tags={"User"}, path="/user/login", summary="Kullanıcı Girişi", security={},
    * @OA\Response(response=200, description="Success"),
    * @OA\Response(response=400,description="Not Found"),
    * @OA\RequestBody(required=true,
    *    @OA\MediaType(mediaType="application/json",
    *    @OA\Schema(required={"email", "password"},
    *       @OA\Property(property="email", type="string", example="user@example.com"),
    *       @OA\Property(property="password", type="string", example="secret123")
    *    ))
    * ))
    */
   public function index() {
      $data = $this->request->json();
      $user = $this->model->login($data);
      if ($user) {
         // $secret = config('defines.secure.jwt_secret');

         $payload = [
            'user_name' => $user->name,
            'user_surname' => $user->name,
            'user_email' => $user->email,
            'exp' => time() + 180000
         ];

         $token = $this->jwt->encode(
            payload: $payload
         );

         return $this->response->json(200, 'user_login', $token);
      } else {
         return $this->response->json(401, 'user_not_found');
      }
   }
}
