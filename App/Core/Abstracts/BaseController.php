<?php

declare(strict_types=1);

namespace App\Core\Abstracts;

use System\View\View;
use System\Http\Request;
use System\Http\Response;
use System\Controller\Controller;

abstract class BaseController extends Controller {
   protected Request $request;
   protected Response $response;
   protected View $view;
   protected int $lang_id;

   final public function language(): int {
      return (int) ($this->request->get('lang_id') ?? 1);
   }

   final public function theme(string $theme): self {
      $this->view->theme($theme);
      return $this;
   }

   final public function import(string $view, array $data = []): void {
      $this->view->import($view, $data);
   }

   final public function view(string $view, array $data = [], int $code = 200): void {
      $this->response->body($this->view->render($view, $data), $code);
   }

   final public function success(mixed $data = null, mixed $message = null, int $code = 200): void {
      $this->response->json($message, $data, null, $code);
   }

   final public function error(mixed $message = null, mixed $error = null, int $code = 400): void {
      if ($message instanceof \Throwable) {
         $code = $message->getCode();
         $message = $message->getMessage();
      }

      $this->response->json($message, null, $error, $code);
   }
}
