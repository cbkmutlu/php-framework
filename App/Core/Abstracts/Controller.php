<?php

declare(strict_types=1);

namespace App\Core\Abstracts;

use System\View\View;
use System\Http\{Request, Response};

abstract class Controller {
   protected Request $request;
   protected Response $response;
   protected View $view;

   /**
    * params
    */
   final protected function params(?string $param = null): array|int|string {
      $result = [
         'language_id' => filter_var($this->request->get('lang'), FILTER_VALIDATE_INT) ?: 1,
         'currency_id' => filter_var($this->request->get('curr'), FILTER_VALIDATE_INT) ?: 1
      ];

      return $result[$param] ?? $result;
   }

   /**
    * theme
    */
   final protected function theme(string $theme): self {
      $this->view->theme($theme);
      return $this;
   }

   /**
    * import
    */
   final protected function import(string $view, array $data = []): void {
      $this->view->import($view, $data);
   }

   /**
    * view
    */
   final protected function view(string $view, array $data = [], int $code = 200): void {
      $this->response->body($this->view->render($view, $data), $code);
   }
}
