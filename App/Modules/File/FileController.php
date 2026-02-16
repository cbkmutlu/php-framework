<?php

declare(strict_types=1);

namespace App\Modules\File;

use System\Http\{Request, Response};
use App\Core\Abstracts\Controller;
use App\Modules\File\FileService;

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
   public function uploadFile(): void {
      $files = $this->request->files('files');
      $result = $this->service->uploadFile($files);

      $this->response->json($result, 201);
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
   public function unlinkFile(): void {
      $json = $this->request->json('path');
      $result = $this->service->unlinkFile($json);

      $this->response->json($result);
   }

   /**
    * @OA\Get(tags={"File"}, path="/file", summary="Dosya proxy",
    *    @OA\Response(response=200, description="Success"),
    *    @OA\Parameter(name="path", in="query", required=false, @OA\Schema(type="string"))
    * )
    */
   public function proxyFile(): void {
      $path = $this->request->get('path');
      $result = $this->service->proxyFile($path);

      $this->response->json($result);
   }
}
