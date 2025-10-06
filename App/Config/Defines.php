<?php

declare(strict_types=1);

return [
   'app' => [
      'migrations' => 'App/Migrations', // App/Modules/*/Migrations
      'routes'     => 'App/Routes', // App/Modules/*/Routes
      'env'        => '.env.local',
      'swagger'    => [
         'user' => '$argon2id$v=19$m=65536,t=4,p=1$QkFYZS5vLjMyclN0cVJNSA$tqbN14XVvOCV6/zry2tOTpnDpAJNrMLOoE+F4oRprxw', // php cli hash 1234
      ]
   ],

   'header' => [
      'allow-origin'      => '*',
      'allow-headers'     => 'Cache-Control, Pragma, Origin, Content-Type, Authorization, X-Requested-With',
      'allow-methods'     => 'GET, POST, PUT, DELETE, OPTIONS',
      'allow-credentials' => false
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
            'db_host'      => getenv('DB_HOST'),
            'db_port'      => getenv('DB_PORT'),
            'db_user'      => getenv('DB_USER'),
            'db_pass'      => getenv('DB_PASS'),
            'db_name'      => getenv('DB_NAME'),
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

   'jwt' => [
      'secret'    => 'TJx+lRD2iNG3t1zugYDK10goO6xWktTpyBCoAOFgNUM=',
      'algorithm' => 'HS256',
      'leeway'    => 0,
      'expire'    => 3600
   ],

   'secure' => [
      'hash_cost'       => 10,
      'hash_algorithm'  => PASSWORD_ARGON2ID,
      'crypt_algorithm' => 'aes-128-cbc',
      'crypt_phrase'    => 'sha256',
      'crypt_key'       => '0dCx9f/3gp+ZfxHD9YXmpEBXfSErV8rY7S7I0bf/qA4='
   ],

   'session' => [
      'encryption_key'   => 'u2LMq1h4oUV0ohL9svqedoB5LebiIE4z',
      'cookie_httponly'  => true,
      'use_only_cookies' => true,
      'lifetime'         => 3600,
      'session_name'     => '_SESSID',
      'samesite'         => 'Lax'
   ],

   'cookie'      => [
      'encryption_key'  => 'HXg1wuVjAOxR7AZZz4rGMbfVwN8nTY20',
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
      'filename'  => 'default-cache',
      'extension' => '.cache',
      'expire'    => 604800
   ],

   'curl' => [
      'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/89.0.142.86 Safari/537.36',
      'redirect'   => true,
      'use_cookie' => false,
      'path'       => 'Storage/curl_cookie.txt'
   ],

   'upload' => [
      'path'          => 'Public' . DS . 'upload',
      'allowed_types' => ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'txt'],
      'allowed_mimes' => ['image/jpeg', 'image/png', 'image/gif', 'application/pdf', 'text/*']
   ],

   'image' => [
      'path'          => 'Public/upload/image',
      'quality'       => 100,
      'background'    => [255, 255, 255, 100]
   ],

   'email' => [
      'server'   => '',
      'port'     => 25,
      'username' => '',
      'userpass' => '',
      'charset'  => 'utf-8'
   ],

   'status' => [
      100 => "HTTP/1.1 100 Continue",
      101 => "HTTP/1.1 101 Switching Protocols",
      200 => "HTTP/1.1 200 OK",
      201 => "HTTP/1.1 201 Created",
      202 => "HTTP/1.1 202 Accepted",
      203 => "HTTP/1.1 203 Non-Authoritative Information",
      204 => "HTTP/1.1 204 No Content",
      205 => "HTTP/1.1 205 Reset Content",
      206 => "HTTP/1.1 206 Partial Content",
      300 => "HTTP/1.1 300 Multiple Choices",
      301 => "HTTP/1.1 301 Moved Permanently",
      302 => "HTTP/1.1 302 Found",
      303 => "HTTP/1.1 303 See Other",
      304 => "HTTP/1.1 304 Not Modified",
      305 => "HTTP/1.1 305 Use Proxy",
      307 => "HTTP/1.1 307 Temporary Redirect",
      400 => "HTTP/1.1 400 Bad Request",
      401 => "HTTP/1.1 401 Unauthorized",
      402 => "HTTP/1.1 402 Payment Required",
      403 => "HTTP/1.1 403 Forbidden",
      404 => "HTTP/1.1 404 Not Found",
      405 => "HTTP/1.1 405 Method Not Allowed",
      406 => "HTTP/1.1 406 Not Acceptable",
      407 => "HTTP/1.1 407 Proxy Authentication Required",
      408 => "HTTP/1.1 408 Request Time-out",
      409 => "HTTP/1.1 409 Conflict",
      410 => "HTTP/1.1 410 Gone",
      411 => "HTTP/1.1 411 Length Required",
      412 => "HTTP/1.1 412 Precondition Failed",
      413 => "HTTP/1.1 413 Request Entity Too Large",
      414 => "HTTP/1.1 414 Request-URI Too Large",
      415 => "HTTP/1.1 415 Unsupported Media Type",
      416 => "HTTP/1.1 416 Requested range not satisfiable",
      417 => "HTTP/1.1 417 Expectation Failed",
      500 => "HTTP/1.1 500 Internal Server Error",
      501 => "HTTP/1.1 501 Not Implemented",
      502 => "HTTP/1.1 502 Bad Gateway",
      503 => "HTTP/1.1 503 Service Unavailable",
      504 => "HTTP/1.1 504 Gateway Time-out"
   ]
];
