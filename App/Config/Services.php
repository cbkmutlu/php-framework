<?php

declare(strict_types=1);

return [
   /**
    * providers
    */
   'providers' => [
      'request'    => System\Http\Request::class,
      'response'   => System\Http\Response::class,
      'curl'       => System\Http\Curl::class,
      'jwt'        => System\Jwt\Jwt::class,
      'event'      => System\Event\Event::class,
      'view'       => System\View\View::class,
      'session'    => System\Session\Session::class,
      'cookie'     => System\Cookie\Cookie::class,
      'cache'      => System\Cache\Cache::class,
      'benchmark'  => System\Benchmark\Benchmark::class,
      'log'        => System\Log\Log::class,
      'validation' => System\Validation\Validation::class,
      'mail'       => System\Mail\Mail::class,
      'database'   => System\Database\Database::class,
      'model'      => System\Model\Model::class,
      'date'       => System\Date\Date::class,
      'upload'     => System\Upload\Upload::class,
      'image'      => System\Libs\Image\Image::class,
      'hash'       => System\Hash\Hash::class,
      'language'   => System\Language\Language::class,
      'csrf'       => System\Csrf\Csrf::class
   ],
   /**
    * middlewares
    */
   'middlewares' => [
      'default' => [
         App\Middlewares\_Security::class
      ],
      'custom' => [
         'Auth' => App\Middlewares\Auth::class,
      ]
   ],
   /**
    * listeners
    */
   'listeners' => [
      'sampleEvent' => [
         App\Listeners\SampleListener::class,
      ]
   ]
];
