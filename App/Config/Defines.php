<?php

declare(strict_types=1);

return [
   'app' => [
      'migrations' => 'App/Migrations',   // App/Modules/*/Migrations
      'routes'     => 'App/Routes',       // App/Modules/*/Routes
      'env'        => '.env.local',
      'swagger'    => [
         // php cli hash 1234
         'user' => '$argon2id$v=19$m=65536,t=4,p=1$QkFYZS5vLjMyclN0cVJNSA$tqbN14XVvOCV6/zry2tOTpnDpAJNrMLOoE+F4oRprxw',
         // php cli hash test
         'test' => '$argon2id$v=19$m=65536,t=4,p=1$bmlCNXJUQTk4MzlvV0tyeg$Y87Nshno7UR19HKlnbAflVIqS3A7ft7pFj9igEWCZ6U'
      ]
   ],

   'language' => [
      'default' => 'tr-TR',
      'locales' => [
         'tr-TR' => [
            'name'      => 'Türkçe',
            'pattern'   => 'd MMMM y EEEE',
            'timezone'  => 'Europe/Istanbul',
            'locale'    => 'tr_TR.UTF-8',
            'date_type' => IntlDateFormatter::FULL,
            'time_type' => IntlDateFormatter::NONE,
            'calendar'  => IntlDateFormatter::GREGORIAN
         ],
         'en-GB' => [
            'name'      => 'English',
            'pattern'   => 'EEEE, MMMM d, yyyy',
            'timezone'  => 'Europe/London',
            'locale'    => 'en_GB.UTF-8',
            'date_type' => IntlDateFormatter::FULL,
            'time_type' => IntlDateFormatter::NONE,
            'calendar'  => IntlDateFormatter::GREGORIAN
         ]
      ]
   ],

   'database' => [
      'default'     => 'primary',
      'persistent'  => false,
      'prepares'    => false,
      'error_mode'  => PDO::ERRMODE_EXCEPTION,
      'fetch_mode'  => PDO::FETCH_ASSOC,
      'stringify'   => false,
      'update_rows' => true,
      'connections' => [
         'primary' => [
            'db_driver'    => 'mysql',
            'db_host'      => get_env('DB_HOST'),
            'db_port'      => get_env('DB_PORT'),
            'db_user'      => get_env('DB_USER'),
            'db_pass'      => get_env('DB_PASS'),
            'db_name'      => get_env('DB_NAME'),
            'db_charset'   => 'utf8mb4',
            'db_collation' => 'utf8mb4_general_ci',
            'db_prefix'    => ''
         ],
         'secondary' => [
            'db_driver'    => 'mysql',
            'db_host'      => '127.0.0.1',
            'db_port'      => '3306',
            'db_user'      => 'root',
            'db_pass'      => 'password',
            'db_name'      => 'database',
            'db_charset'   => 'utf8mb4',
            'db_collation' => 'utf8mb4_general_ci',
            'db_prefix'    => ''
         ]
      ]
   ],

   'header' => [
      'allow-origin'      => '*',
      'allow-headers'     => 'Cache-Control, Pragma, Origin, Content-Type, Authorization, X-Requested-With',
      'allow-methods'     => 'GET, POST, PUT, DELETE, PATCH, OPTIONS',
      'allow-credentials' => false
   ],

   'middlewares' => [
      App\Core\Middlewares\Security::class
   ],

   'listeners' => [
      'sampleEvent' => [
         App\Core\Listeners\SampleListener::class
      ]
   ],

   'jwt' => [
      'secret'    => '57f346f52d7828a0ece45560faba0acb04db656d51369a6228adc3fb668911fd',
      'algorithm' => 'HS256',
      'leeway'    => 0,
      'expire'    => [
         'access'  => 3600,    // 1 hour in seconds
         'refresh' => 5184000  // 60 days in seconds
      ]
   ],

   'rate_limit' => [
      'max_attempts'  => 10,     // maximum number of requests per minute
      'decay_minutes' => 5,      // reset time (minutes)
      'enabled'       => true,   // rate limiting enabled/disabled
      'whitelist'     => [
         '127.0.0.1',
         '::1'
      ],
      'custom_limits' => [
         '/v1/auth/login' => [
            'max_attempts'  => 10,
            'decay_minutes' => 5
         ],
         '/v1/auth/register' => [
            'max_attempts'  => 5,
            'decay_minutes' => 5
         ]
      ]
   ],

   'crypt' => [
      'secret'    => '5600f961b1e548a4097e17a1d36d2e9c6c3066d65d6983d31b7b0eacde545e2d',
      'cipher'    => 'aes-128-cbc',
      'phrase'    => 'sha256',
      'cost'      => 10,
      'algorithm' => PASSWORD_ARGON2ID
   ],

   'session' => [
      'encryption_key'   => '15d677580ffde727ca0d8c4046fbe7ee40f9df6da258d71ac631ab488845f127',
      'cookie_httponly'  => true,
      'use_only_cookies' => true,
      'lifetime'         => 3600,
      'session_name'     => '_SESSID',
      'samesite'         => 'Lax'
   ],

   'cookie'      => [
      'encryption_key'  => 'c7923454ea96676f419744b3fc2d202fcfee1881847c27debf048c7372020f56',
      'cookie_security' => true,
      'separator'       => '--',
      'httponly'        => true,
      'secure'          => false,
      'path'            => '/',
      'domain'          => '',
      'samesite'        => 'Lax'
   ],

   'log' => [
      'path'           => 'Storage/Logs',
      'prefix'         => 'Log_',
      'file_format'    => 'Y-m-d',
      'content_format' => 'H:i:s',
      'extension'      => '.log'
   ],

   'cache' => [
      'path'      => 'Storage/Cache',
      'namespace' => 'app',
      'extension' => '.cache',
      'expire'    => 604800
   ],

   'curl' => [
      'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/89.0.142.86 Safari/537.36',
      'redirect'   => true,
      'use_cookie' => false,
      'path'       => 'Storage/curl_cookie.txt'
   ],

   'image' => [
      'path'       => 'Public/upload/image',
      'quality'    => 100,
      'background' => [255, 255, 255, 100]
   ],

   'upload' => [
      'default' => 'local',
      'allowed_types' => ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'txt'],
      'allowed_mimes' => ['image/jpeg', 'image/png', 'image/gif', 'application/pdf', 'text/*'],
      'local' => [
         'path'    => 'Public/upload',
         'allowed_types' => ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'txt'], // override
         'allowed_mimes' => ['image/jpeg', 'image/png', 'image/gif', 'application/pdf', 'text/*'], // override
         'adapter' => App\Core\Adapters\UploadLocalAdapter::class
      ],
      'cloudflare' => [
         'path'              => '',
         'account_id'        => get_env('CF_ACCOUNT_ID'),
         'access_key_id'     => get_env('CF_ACCESS_KEY_ID'),
         'access_key_secret' => get_env('CF_ACCESS_KEY_SECRET'),
         'bucket_name'       => get_env('CF_BUCKET_NAME'),
         'endpoint'          => get_env('CF_ENDPOINT'),
         'public_dev_url'    => get_env('CF_PUBLIC_DEV_URL'),
         'cdn_url'           => get_env('CDN_URL') ?: null
      ]
   ],

   'email' => [
      'server'   => '',
      'port'     => 25,
      'username' => '',
      'userpass' => '',
      'charset'  => 'utf-8',
      'from'     => [
         'name'    => '',
         'address' => ''
      ]
   ],

   'providers' => [
      'benchmark'  => [System\Benchmark\Benchmark::class, true],
      'cache'      => [System\Cache\Cache::class, true],
      'cookie'     => System\Cookie\Cookie::class,
      'curl'       => System\Curl\Curl::class,
      'database'   => [System\Database\Database::class, true],
      'date'       => System\Date\Date::class,
      'event'      => [System\Event\Event::class, true],
      'request'    => [System\Http\Request::class, true],
      'response'   => [System\Http\Response::class, true],
      'image'      => System\Image\Image::class,
      'jwt'        => [System\Jwt\Jwt::class, true],
      'language'   => [System\Language\Language::class, true],
      'log'        => [System\Log\Log::class, true],
      'mail'       => System\Mail\Mail::class,
      'pagination' => System\Pagination\Pagination::class,
      'crypt'      => [System\Crypt\Crypt::class, true],
      'session'    => [System\Session\Session::class, true],
      'similarity' => System\Text\Similarity::class,
      'upload'     => System\Upload\Upload::class,
      'validation' => System\Validation\Validation::class,
      'view'       => [System\View\View::class, true],
      'error'      => [System\Exception\ExceptionHandler::class, true]
   ],

   'status' => [
      100 => 'HTTP/1.1 100 Continue',
      101 => 'HTTP/1.1 101 Switching Protocols',
      200 => 'HTTP/1.1 200 OK',
      201 => 'HTTP/1.1 201 Created',
      202 => 'HTTP/1.1 202 Accepted',
      203 => 'HTTP/1.1 203 Non-Authoritative Information',
      204 => 'HTTP/1.1 204 No Content',
      205 => 'HTTP/1.1 205 Reset Content',
      206 => 'HTTP/1.1 206 Partial Content',
      300 => 'HTTP/1.1 300 Multiple Choices',
      301 => 'HTTP/1.1 301 Moved Permanently',
      302 => 'HTTP/1.1 302 Found',
      303 => 'HTTP/1.1 303 See Other',
      304 => 'HTTP/1.1 304 Not Modified',
      305 => 'HTTP/1.1 305 Use Proxy',
      307 => 'HTTP/1.1 307 Temporary Redirect',
      400 => 'HTTP/1.1 400 Bad Request',
      401 => 'HTTP/1.1 401 Unauthorized',
      402 => 'HTTP/1.1 402 Payment Required',
      403 => 'HTTP/1.1 403 Forbidden',
      404 => 'HTTP/1.1 404 Not Found',
      405 => 'HTTP/1.1 405 Method Not Allowed',
      406 => 'HTTP/1.1 406 Not Acceptable',
      407 => 'HTTP/1.1 407 Proxy Authentication Required',
      408 => 'HTTP/1.1 408 Request Time-out',
      409 => 'HTTP/1.1 409 Conflict',
      410 => 'HTTP/1.1 410 Gone',
      411 => 'HTTP/1.1 411 Length Required',
      412 => 'HTTP/1.1 412 Precondition Failed',
      413 => 'HTTP/1.1 413 Request Entity Too Large',
      414 => 'HTTP/1.1 414 Request-URI Too Large',
      415 => 'HTTP/1.1 415 Unsupported Media Type',
      416 => 'HTTP/1.1 416 Requested range not satisfiable',
      417 => 'HTTP/1.1 417 Expectation Failed',
      500 => 'HTTP/1.1 500 Internal Server Error',
      501 => 'HTTP/1.1 501 Not Implemented',
      502 => 'HTTP/1.1 502 Bad Gateway',
      503 => 'HTTP/1.1 503 Service Unavailable',
      504 => 'HTTP/1.1 504 Gateway Time-out'
   ]
];
