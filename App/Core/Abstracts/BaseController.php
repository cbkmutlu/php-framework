<?php

declare(strict_types=1);

namespace App\Core\Abstracts;

use System\View\View;
use System\Http\Request;
use System\Http\Response;
use App\Core\Enums\LanguageEnum;

abstract class BaseController {
   protected Request $request;
   protected Response $response;
   protected View $view;
   protected ?int $lang_id;

   final protected function language(): int {
      if (isset($this->lang_id)) {
         return $this->lang_id;
      }

      $config = import_config('defines.language');
      $this->lang_id = (int) ($this->request->get('lang_id') ?? LanguageEnum::resolve($config['default']));

      return $this->lang_id;
   }

   final protected function theme(string $theme): self {
      $this->view->theme($theme);
      return $this;
   }

   final protected function import(string $view, array $data = []): void {
      $this->view->import($view, $data);
   }

   final protected function view(string $view, array $data = [], int $code = 200): void {
      $this->response->body($this->view->render($view, $data), $code);
   }

   final protected function middleware(array $middlewares = []): void {
      $services = import_config('services.middlewares.custom');
      foreach ($middlewares as $middleware) {
         $middleware = ucfirst($middleware);
         if (isset($services[$middleware]) && class_exists($services[$middleware])) {
            call_user_func_array([new $services[$middleware], 'handle'], []);
         }
      }
   }

   /**
    * Ortak API response handler fonksiyonu.
    *
    * Verilen callback fonksiyonunu çalıştırır, başarılı sonuç dönerse HTTP JSON response olarak başarılı mesaj ve sonucu döner.
    *
    * @param callable $callback çalıştırılacak işlem/fonksiyon
    * @param mixed|null $message başarı durumunda dönecek mesaj (string veya başka tür)
    * @param int $code başarılı durum için HTTP status kodu (varsayılan 200)
    *
    * @throws \Throwable hata oluşursa üst katmana fırlatır
    */
   final protected function response(callable $callback, mixed $message = null, int $code = 200): void {
      try {
         $result = $callback();
         $this->response->json($message, $result, null, $code);
      } catch (\Throwable $th) {
         throw $th;
      }
   }
}
