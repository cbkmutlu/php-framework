<?php

declare(strict_types=1);

return [
   'providers' => [
      'benchmark'  => System\Benchmark\Benchmark::class,
      'cache'      => [System\Cache\Cache::class, true],
      'cookie'     => System\Cookie\Cookie::class,
      'database'   => [System\Database\Database::class, true],
      'date'       => System\Date\Date::class,
      'event'      => System\Event\Event::class,
      'request'    => [System\Http\Request::class, true],
      'response'   => [System\Http\Response::class, true],
      'curl'       => System\Http\Curl::class,
      'image'      => System\Image\Image::class,
      'jwt'        => System\Jwt\Jwt::class,
      'language'   => System\Language\Language::class,
      'log'        => [System\Log\Log::class, true],
      'mail'       => System\Mail\Mail::class,
      'pagination' => System\Pagination\Pagination::class,
      'crypt'      => System\Secure\Crypt::class,
      'csrf'       => System\Secure\Csrf::class,
      'hash'       => System\Secure\Hash::class,
      'session'    => [System\Session\Session::class, true],
      'similarity' => System\Text\Similarity::class,
      'upload'     => System\Upload\Upload::class,
      'validation' => System\Validation\Validation::class,
      'view'       => System\View\View::class
   ],

   'middlewares' => [
      'default' => [
         App\Middlewares\_Security::class
      ],
      'custom' => [
         'Auth' => App\Middlewares\Auth::class
      ]
   ],

   'listeners' => [
      'sampleEvent' => [
         App\Listeners\SampleListener::class
      ]
   ]
];
