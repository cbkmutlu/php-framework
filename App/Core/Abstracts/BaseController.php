<?php

declare(strict_types=1);

namespace App\Core\Abstracts;

use System\Http\Request;
use System\Http\Response;
use System\Controller\Controller;
use System\Exception\SystemException;
use System\View\View;

abstract class BaseController extends Controller {
   protected Request $request;
   protected Response $response;
   protected View $view;

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

   final function result(object $callback) {
      try {
         $result = $callback();
         return $this->success($result);
      } catch (SystemException $e) {
         return $this->error($e);
      }
   }
}
