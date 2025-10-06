<?php

declare(strict_types=1);

return [
   'providers' => [
      'benchmark'  => System\Benchmark\Benchmark::class,
      'cache'      => [System\Cache\Cache::class, true],
      'cookie'     => System\Cookie\Cookie::class,
      'curl'       => System\Curl\Curl::class,
      'database'   => [System\Database\Database::class, true],
      'date'       => System\Date\Date::class,
      'event'      => System\Event\Event::class,
      'request'    => [System\Http\Request::class, true],
      'response'   => [System\Http\Response::class, true],
      'image'      => System\Image\Image::class,
      'jwt'        => System\Jwt\Jwt::class,
      'language'   => [System\Language\Language::class, true],
      'log'        => [System\Log\Log::class, true],
      'mail'       => System\Mail\Mail::class,
      'pagination' => System\Pagination\Pagination::class,
      'secure'     => System\Secure\Secure::class,
      'session'    => [System\Session\Session::class, true],
      'similarity' => System\Text\Similarity::class,
      'upload'     => System\Upload\Upload::class,
      'validation' => System\Validation\Validation::class,
      'view'       => System\View\View::class,
      'error'      => System\Exception\ExceptionHandler::class
   ],

   'middlewares' => [
      'default' => [
         App\Core\Middlewares\_Security::class
      ],
      'custom' => [
         'Auth' => App\Core\Middlewares\Auth::class
      ]
   ],

   'listeners' => [
      'sampleEvent' => [
         App\Core\Listeners\SampleListener::class
      ]
   ]
];
