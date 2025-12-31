<?php

declare(strict_types=1);

namespace App\Modules\File;

use System\Http\Request;
use System\Http\Response;
use App\Modules\File\FileService;
use App\Core\Abstracts\Controller;

/**
 * @OA\Tag(name="File", description="Dosya işlemleri")
 */
class FileController extends Controller {
   public function __construct(
      protected Response $response,
      protected Request $request,
      protected FileService $service
   ) {
   }

   /**
    * @OA\Post(tags={"File"}, path="/file", summary="Dosya yükle",
    *    @OA\Response(response=201, description="Success"),
    *    @OA\RequestBody(required=true, @OA\MediaType(mediaType="multipart/form-data",
    *       @OA\Schema(required={"files[]"},
    *          @OA\Property(property="files[]", type="array", @OA\Items(type="string", format="binary"))
    *       )
    *    ))
    * )
    */
   public function uploadFile() {
      $this->response(function () {
         $files = $this->request->files('files');
         return $this->service->uploadFile($files);
      }, code: 201);
   }

   /**
    * @OA\Patch(tags={"File"}, path="/file", summary="Dosya sil",
    *    @OA\Response(response=200, description="Success"),
    *    @OA\RequestBody(required=true, @OA\JsonContent(
    *       required={"path"},
    *       @OA\Property(property="path", type="string", example="image.png"),
    *    ))
    * )
    */
   public function unlinkFile() {
      $this->response(function () {
         $json = $this->request->json('path');
         return $this->service->unlinkFile($json);
      });
   }

   /**
    * @OA\Get(
    *    tags={"File"}, path="/file", summary="Dosya proxy",
    *    @OA\Response(response=200, description="Success"),
    *    @OA\Parameter(name="path", in="query", required=false, @OA\Schema(type="string"))
    * )
    */
   public function proxyFile() {
      $this->response(function () {
         return $this->service->proxyFile($this->request->get());
      });
   }
}
