<?php

declare(strict_types=1);

namespace App\Modules\Customer\Controllers;

use App\Modules\Customer\Models\ListModel;
use System\Controller\Controller;
use System\Http\Response;

/**
 * @OA\Tag(name="Customers", description="Customer related operations")
 */
class ListController extends Controller {

   public function __construct(
      private Response $response,
      private ListModel $model
   ) {
   }

   /**
    * @OA\Get(tags={"Customers"}, path="/customer/list", summary="Satışları listele",
    * @OA\Response(response=200, description="Success"),
    * @OA\Response(response=400, description="Not Found"))
    */
   public function index() {
      $model = $this->model->index();

      return $this->response->json(200, 'customer_list', $model);
   }
}
