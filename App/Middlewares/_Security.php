<?php

declare(strict_types=1);

namespace App\Middlewares;

use System\Http\Request;
use System\Http\Response;

class _Security {

   public function __construct(private Request $request, private Response $response) {
   }

   public function handle() {
      header("Content-Security-Policy: default-src 'self'; script-src 'self'");

      if ($this->request->isRobot()) {
         $this->response->json(403, "request_is_robot");
         exit();
      }

      if (!$this->request->isUri()) {
         $this->response->json(400, "request_is_not_valid_uri");
         exit();
      }
   }
}
