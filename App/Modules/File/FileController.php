<?php

declare(strict_types=1);

namespace App\Modules\File;

use System\Http\Request;
use System\Http\Response;
use App\Modules\File\FileService;
use App\Modules\File\FileRequest;
use App\Core\Abstracts\BaseController;

/**
 * @OA\Tag(name="File", description="Dosya işlemleri")
 */
class FileController extends BaseController {
   public function __construct(
      protected Response $response,
      protected Request $request,
      protected FileService $service
   ) {
   }

   /**
    * @OA\Post(tags={"File"}, path="/file/", summary="Dosya yükle",
    *    @OA\Response(response=201, description="Success"),
    *    @OA\RequestBody(required=true,
    *       @OA\MediaType(mediaType="multipart/form-data",
    *          @OA\Schema(
    *             required={"path", "files[]"},
    *             @OA\Property(property="path", type="string", example="/files/products"),
    *             @OA\Property(property="files[]", type="array",
    *                @OA\Items(type="string", format="binary"),
    *                description="Upload multiple files"
    *             )
    *          )
    *       )
    *    )
    * )
    */
   public function uploadFile() {
      $this->response(function () {
         $files = $this->request->files();
         $path = $this->request->post('path');

         $request = new FileRequest();
         $request->fromArray(['files' => $files, 'path' => $path]);
         $result = $this->service->upload($request->files, $request->path);

         return $result;
      }, code: 201);
   }

   /**
    * @OA\Patch(tags={"File"}, path="/file/", summary="Dosya sil",
    *    @OA\Response(response=200, description="Success"),
    *    @OA\RequestBody(required=true, @OA\JsonContent(
    *       required={"path"},
    *       @OA\Property(property="path", type="string", example="image.png"),
    *    ))
    * )
    */
   public function unlinkFile() {
      $this->response(function () {
         $json = $this->request->json();

         $request = new FileRequest();
         $request->fromArray($json);
         $result = $this->service->unlink($request->toArray());

         return $result;
      });
   }
}
