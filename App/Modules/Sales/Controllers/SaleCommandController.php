<?php

declare(strict_types=1);

namespace App\Modules\Sales\Controllers;

use App\Modules\Sales\Models\SaleCommandModel;
use DateTime;
use System\Controller\Controller;
use System\Http\Request;
use System\Http\Response;

/**
 * @OA\Tag(name="Sales", description="Sales related operations")
 */
class SaleCommandController extends Controller {
   public function __construct(
      private Request $request,
      private Response $response,
      private SaleCommandModel $model,
      private SaleInstallmentController $saleInstallmentController,
      private DateTime $date
   ) {
   }

   /**
    * @OA\Post(tags={"Sales"}, path="/sales/add", summary="Satışları ekle",
    * @OA\Response(response=200, description="Success"),
    * @OA\Response(response=400, description="Not Found"),
    * @OA\RequestBody(required=true,
    *    @OA\MediaType(mediaType="application/json",
    *    @OA\Schema(required={"customer_id", "product", "price", "installment", "installment_type"},
    *       @OA\Property(property="customer_id", type="integer"),
    *       @OA\Property(property="product", type="string"),
    *       @OA\Property(property="price", type="float"),
    *       @OA\Property(property="installment", type="integer"),
    *       @OA\Property(property="installment_type", type="string")
    *    ))
    * ))
    */
   public function add() {
      $data = $this->request->json();
      $installmentCount = $data['installment'];

      $sale = $this->model->add($data);

      if ($sale) {
         $price = floor($data['price'] / $installmentCount);
         $floor = $price * $installmentCount;
         $remain = $data['price'] - $floor;
         $lastInstallment = $remain + $price;

         for ($i = 0; $i < $installmentCount; $i++) {
            $arr = array(
               'sale_id' => $sale,
               'price' => ($i == $installmentCount - 1) ? $lastInstallment : $price,
               'payment' => 0,
               'due_at' => $this->date->modify('+' . 1 . ' ' . $data['installment_type'])->format('Y-m-d')
            );

            $this->saleInstallmentController->add($arr);
         }

         return $this->response->json(200, 'sale_added', $sale);
      } else {
         return $this->response->json(400, 'sale_not_added');
      }
   }


   /**
    * @OA\Delete(tags={"Sales"}, path="/sales/delete/{id}", summary="Satış Sil",
    * @OA\Response(response=200, description="Success"),
    * @OA\Response(response=400, description="Not Found"),
    * @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")))
    */
   public function delete(int $id) {
      $delete = $this->model->delete($id);

      if ($delete) {
         return $this->response->json(200, 'sale_deleted');
      } else {
         return $this->response->json(400, 'sale_not_deleted');
      }
   }

   /**
    * @OA\Put(tags={"Sales"}, path="/sales/update", summary="Satışları güncelle",
    * @OA\Response(response=200, description="Success"),
    * @OA\Response(response=400, description="Not Found"),
    * @OA\RequestBody(required=true,
    *    @OA\MediaType(mediaType="application/json",
    *    @OA\Schema(required={"id", "customer_id", "product", "price", "installment", "installment_type"},
    *       @OA\Property(property="id", type="integer"),
    *       @OA\Property(property="customer_id", type="integer"),
    *       @OA\Property(property="product", type="string"),
    *       @OA\Property(property="price", type="float"),
    *       @OA\Property(property="installment", type="integer"),
    *       @OA\Property(property="installment_type", type="string")
    *    ))
    * ))
    */
   public function update() {
      $data = $this->request->json();
      $update = $this->model->update($data);

      if ($update) {
         return $this->response->json(200, 'sale_updated', $update);
      } else {
         return $this->response->json(400, 'sale_not_updated');
      }
   }
}
