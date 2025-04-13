<?php

declare(strict_types=1);

namespace App\Modules\Customer\Controllers;

use App\Modules\Customer\Models\CommandModel;
use System\Controller\Controller;
use System\Http\Request;
use System\Http\Response;

/**
 * @OA\Tag(name="Customers", description="Customer related operations")
 */
class CommandController extends Controller {

   public function __construct(
      private Request $request,
      private Response $response,
      private CommandModel $model
   ) {
   }

   /**
    * @OA\Post(tags={"Customers"}, path="/customer/add", summary="Müşteri ekle",
    * @OA\Response(response=200, description="Success"),
    * @OA\Response(response=400, description="Not Found"),
    * @OA\RequestBody(
    *    required=true,
    *    @OA\MediaType(mediaType="application/json",
    *       @OA\Schema(required={"name", "surname", "email", "phone", "tckn", "address", "notes"},
    *          @OA\Property(property="name", type="string"),
    *          @OA\Property(property="surname", type="string"),
    *          @OA\Property(property="email", type="string"),
    *          @OA\Property(property="phone", type="integer"),
    *          @OA\Property(property="tckn", type="integer"),
    *          @OA\Property(property="address", type="string"),
    *          @OA\Property(property="notes", type="string")
    *       ))
    *    )
    * )
    */
   public function add() {
      $data = $this->request->json();
      $customer = $this->model->add($data);

      if ($customer) {
         return $this->response->json(200, 'customer_added', $customer);
      } else {
         return $this->response->json(400, 'customer_not_added');
      }
   }

   /**
    * @OA\Delete(tags={"Customers"}, path="/customer/delete/{id}", summary="Müşteri Sil",
    * @OA\Response(response=200, description="Success"),
    * @OA\Response(response=400, description="Not Found"),
    * @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")))
    */
   public function delete(int $id) {
      $delete = $this->model->delete($id);

      if ($delete) {
         return $this->response->json(200, 'customer_deleted');
      } else {
         return $this->response->json(400, 'customer_not_deleted');
      }
   }

   /**
    * @OA\Put(tags={"Customers"}, path="/customer/update", summary="Müşteri güncelle",
    * @OA\Response(response=200, description="Success"),
    * @OA\Response(response=400, description="Not Found"),
    * @OA\RequestBody(required=true,
    *    @OA\MediaType(mediaType="application/json",
    *    @OA\Schema(required={"id", "name", "surname", "email", "phone", "tckn", "address", "notes"},
    *       @OA\Property(property="id", type="integer"),
    *       @OA\Property(property="name", type="string"),
    *       @OA\Property(property="surname", type="string"),
    *       @OA\Property(property="email", type="string"),
    *       @OA\Property(property="phone", type="integer"),
    *       @OA\Property(property="tckn", type="integer"),
    *       @OA\Property(property="address", type="string"),
    *       @OA\Property(property="notes", type="string")
    *    ))
    * ))
    */
   public function update() {
      $data = $this->request->json();
      $update = $this->model->update($data);

      if ($update) {
         return $this->response->json(200, 'customer_updated', $update);
      } else {
         return $this->response->json(400, 'customer_not_updated');
      }
   }
}
