<?php

declare(strict_types=1);

namespace App\Modules\Sales\Controllers;

use App\Modules\Sales\Models\SaleListModel;
use System\Controller\Controller;
use System\Http\Response;

class SaleListController extends Controller {
   public function __construct(
      private Response $response,
      private SaleListModel $model
   ) {
   }

   /**
    * @OA\Get(tags={"Sales"}, path="/sales/list", summary="Satışları listele",
    * @OA\Response(response=200, description="Success"),
    * @OA\Response(response=400,description="Not Found"))
    */
   public function index() {
      $model = $this->model->index();

      return $this->response->json(200, 'sales_list', $model);
   }
}
