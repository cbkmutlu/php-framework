<?php

declare(strict_types=1);

namespace App\Modules\Sales\Controllers;

use App\Modules\Sales\Models\SaleInstallmentModel;
use System\Controller\Controller;
use System\Http\Response;

class SaleInstallmentController extends Controller {
   public function __construct(
      private Response $response,
      private SaleInstallmentModel $saleInstallment
   ) {
   }

   public function add(array $data) {

      $installment = $this->saleInstallment->add($data);

      if ($installment) {
         return $this->response->json(200, 'installment_added', $installment);
      } else {
         return $this->response->json(400, 'installment_not_added');
      }
   }
}
