<?php

declare(strict_types=1);

namespace App\Modules\Image;

use System\Http\Request;
use System\Http\Response;
use App\Modules\Image\ImageService;
use App\Modules\Image\ImageRequest;
use App\Core\Abstracts\BaseController;

/**
 * @OA\Tag(name="Image", description="Resim işlemleri")
 */
class ImageController extends BaseController {
   public function __construct(
      protected Response $response,
      protected Request $request,
      protected ImageService $service
   ) {
   }

   /**
    * @OA\Post(tags={"Image"}, path="/image/create", summary="Resim yükle",
    *    @OA\Response(response=201, description="Success"),
    *    @OA\RequestBody(required=true,
    *       @OA\MediaType(mediaType="multipart/form-data",
    *          @OA\Schema(
    *             required={"path", "files[]"},
    *             @OA\Property(property="path", type="string", example="/images/products"),
    *             @OA\Property(property="files[]", type="array",
    *                @OA\Items(type="string", format="binary"),
    *                description="Upload multiple files"
    *             )
    *          )
    *       )
    *    )
    * )
    */
   public function uploadImage() {
      $this->response(function () {
         $files = $this->request->files();
         $path = $this->request->post('path');

         $request = new ImageRequest();
         $request->fromArray(['files' => $files, 'path' => $path]);
         $result = $this->service->upload($request->files, $request->path);

         return $result;
      }, code: 201);
   }

   /**
    * @OA\Post(tags={"Image"}, path="/image/delete", summary="Resim sil",
    *    @OA\Response(response=200, description="Success"),
    *    @OA\RequestBody(required=true, @OA\JsonContent(
    *       required={"id", "table"},
    *       @OA\Property(property="id", type="integer", example=1),
    *       @OA\Property(property="table", type="string", example="product"),
    *       @OA\Property(property="unlink", type="boolean", example=true),
    *       @OA\Property(property="delete", type="boolean", example=false)
    *    ))
    * )
    */
   public function deleteImage() {
      $this->response(function () {
         $json = $this->request->json();

         $request = new ImageRequest();
         $request->fromArray($json);
         $result = $this->service->unlink($request->toArray());

         return $result;
      });
   }
}
