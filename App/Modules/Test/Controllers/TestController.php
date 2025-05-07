<?php

declare(strict_types=1);

namespace App\Modules\Test\Controllers;

use App\Core\Abstracts\BaseController;
use App\Modules\Test\Services\TestService;
use System\Http\Request;
use System\Http\Response;
use System\Benchmark\Benchmark;
use System\View\View;

/**
 * @OA\Tag(name="Test", description="Test işlemler")
 */
class TestController extends BaseController {
   public function __construct(
      protected Request $request,
      protected Response $response,
      protected View $view,
      protected Benchmark $benchmark,
      private TestService $service
   ) {
   }

   /**
    * @OA\Get(tags={"Test"}, path="/test/", summary="Liste",
    *    @OA\Response(response=200, description="Success")
    * )
    */
   public function getAllUser() {
      $result = $this->service->getAllUser();
      return $this->success($result);
   }

   /**
    * @OA\Get(tags={"Test"}, path="/test/{id}", summary="Detayı (ID'ye göre)",
    *    @OA\Response(response=200, description="Success"),
    *    @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer"))
    * )
    */
   public function getUser(int $id) {
      $result = $this->service->getUser($id);
      return $this->success($result);
   }

   /**
    * @OA\Post(tags={"Test"}, path="/test/", summary="Giriş",
    *    @OA\Response(response=200, description="Success"),
    *    @OA\RequestBody(required=true, @OA\JsonContent(
    *       required={"username", "password"},
    *       @OA\Property(property="username", type="string", example="user@example.com"),
    *       @OA\Property(property="password", type="string", example="secret123")
    *    ))
    * )
    */
   public function postLogin() {
      $data = $this->request->json();
      if (is_null($data)) {
         // throw new SystemException("No data provided", 400);
         return $this->error("No data provided", null, 400);
      }

      $result = $this->service->loginUser($data);
      return $this->success($result);

      // return $this->result(function () use ($data) {
      //    return $this->service->loginUser($data);
      // });
   }

   /**
    * @OA\Put(tags={"Test"}, path="/test/", summary="Güncelle",
    * @OA\Response(response=200, description="Success"),
    *    @OA\RequestBody(required=true, @OA\JsonContent(
    *       required={"id", "email", "password"},
    *       @OA\Property(property="id", type="integer", example="1"),
    *       @OA\Property(property="email", type="string", example="user@example.com"),
    *       @OA\Property(property="password", type="string", example="secret123")
    *    ))
    * )
    */
   public function putUser() {
      $data = $this->request->json();
      if (is_null($data)) {
         return $this->error("No data provided", null, 400);
      }

      $result = $this->service->updateUser($data);
      return $this->success($result);
   }

   /**
    * @OA\Delete(
    *    tags={"Test"}, path="/test/{id}", summary="Sil (from path hardDelete)",
    *    @OA\Response(response=200, description="Success"),
    *    @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer"))
    * )
    */
   public function hardDeleteUser(int $id) {
      $result = $this->service->hardDeleteUser($id);
      return $this->success($result);
   }

   /**
    * @OA\Delete(
    *    tags={"Test"}, path="/test/", summary="Sil (from query softDelete)",
    *    @OA\Response(response=200, description="Success"),
    *    @OA\Parameter(name="id", in="query", required=true, @OA\Schema(type="integer"))
    * )
    */
   public function softDeleteUser() {
      $id = $this->request->get('id');
      $result = $this->service->softDeleteUser((int) $id);
      return $this->success($result);
   }


   /**
    * @OA\Get(tags={"Test"}, path="/test/benchmark/", summary="Benchmark",
    *    @OA\Response(response=200, description="Success")
    * )
    */
   public function getBenchmark() {
      $result = $this->service->getBenchmark();
      // return $this->theme('html')->view('Test@body.html', $result);
      // return $this->theme('php')->import('Test@test.php', $result);
      return $this->success($result);
   }
}
