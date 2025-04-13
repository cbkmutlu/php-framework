<?php

declare(strict_types=1);

namespace App\Modules\Customer\Controllers;

use App\Modules\Customer\Models\ProfileModel;
use System\Controller\Controller;
use System\Http\Response;

class ProfileController extends Controller {

   public function __construct(
      private Response $response,
      private ProfileModel $model
   ) {
   }

   public function index(int $id) {
      $profile = $this->model->index($id);
      return $this->response->json(200, 'customer_profile', $profile);
   }
}
