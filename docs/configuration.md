# Konfigürasyon ve Çalışma Ortamı

Bu belge framework'ün konfigürasyon yönetimini, environment değişkenlerini ve development/production ayrımlarını açıklar.

## 📁 Konfigürasyon Dosyaları

### 1. `App/Config/Constants.php` - Dizin Sabitleri

Bootstrap'in en başında yüklenir (`Public/index.php`, `cli`):

```php
<?php
declare(strict_types=1);

define('ROOT_DIR', realpath(__DIR__ . '/../../') . '/');
define('APP_DIR', ROOT_DIR . 'App/');
define('SYSTEM_DIR', ROOT_DIR . 'System/');
define('PUBLIC_DIR', ROOT_DIR . 'Public/');
define('BASE_URL', rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/'));
```

| Sabit        | Açıklama                       |
| ------------ | ------------------------------ |
| `ROOT_DIR`   | Proje kök dizini               |
| `APP_DIR`    | `App/` dizini                  |
| `SYSTEM_DIR` | `System/` dizini               |
| `PUBLIC_DIR` | `Public/` dizini               |
| `BASE_URL`   | Script'in çalıştığı URL path'i |

### 2. `App/Config/Defines.php` - Ana Konfigürasyon

Framework'ün merkezi konfigürasyon dosyası. `import_config('defines.key')` ile erişilir.

```php
return [
    'app' => [                    // import_config('defines.app')
        'migrations' => 'App/Migrations',
        'routes'     => 'App/Routes',
        'seeds'      => 'App/Seeds',
        'env'        => '.env.production',  // Hangi .env dosyası yüklenecek
        'swagger'    => [ ... ],             // Swagger basic auth credentials
    ],

    'language' => [ ... ],        // import_config('defines.language')
    'database' => [ ... ],        // import_config('defines.database')
    'header' => [ ... ],          // import_config('defines.header') - CORS headers
    'jwt' => [ ... ],             // import_config('defines.jwt')
    'rate_limit' => [ ... ],      // import_config('defines.rate_limit')
    'crypt' => [ ... ],           // import_config('defines.crypt')
    'session' => [ ... ],         // import_config('defines.session')
    'cookie' => [ ... ],          // import_config('defines.cookie')
    'log' => [ ... ],             // import_config('defines.log')
    'cache' => [ ... ],           // import_config('defines.cache')
    'curl' => [ ... ],            // import_config('defines.curl')
    'image' => [ ... ],           // import_config('defines.image')
    'gate' => [ ... ],            // import_config('defines.gate')
    'upload' => [ ... ],          // import_config('defines.upload')
    'email' => [ ... ],           // import_config('defines.email')
    'middlewares' => [ ... ],     // import_config('defines.middlewares')
    'listeners' => [ ... ],       // import_config('defines.listeners')
    'providers' => [ ... ],       // import_config('defines.providers') - Container providers
    'status' => [ ... ],          // HTTP status codes
];
```

**Erişim Örnekleri:**

```php
import_config('defines.app.migrations');     // 'App/Migrations'
import_config('defines.jwt.expire.access');  // 3600
import_config('defines.database.connections.primary.db_host');  // get_env('DB_HOST') -> env'den gelir
```

### 3. `App/Config/Authorization.php` - Roller ve İzinler

Seed ve Gate sistemi tarafından kullanılır:

```php
return [
    'roles' => [
        'super-admin' => ['scope' => 'system', 'display_name' => 'Super Administrator', 'permissions' => '*'],
        'admin'       => ['scope' => 'system', 'display_name' => 'Administrator', 'permissions' => '*'],
    ],
    'users' => ['1' => 'super-admin'],  // user_id => role_slug
    'permissions' => [
        'product:viewAny' => 'View Any Product',
        'product:create'  => 'Create Product',
        // ...
    ],
];
```

### 4. `App/Config/Approval.php` - Onay Süreçleri

`ApprovalService` tarafından kullanılır:

```php
return [
    'templates' => [
        'product' => [
            'create' => [
                ['assignee_type' => 'role', 'assignee_id' => 'admin', 'step' => 1],
                ['assignee_type' => 'role', 'assignee_id' => 'admin', 'step' => 2],
            ],
        ],
    ],
    'callbacks' => [
        'product' => [
            'create' => [ProductService::class, 'executeApprovedCreate'],
        ],
    ],
];
```

---

## 🌍 Environment Değişkenleri

### `.env.development` / `.env.production`

Bu dosyalar **hassas bilgiler** içerir (DB şifreleri, API key'ler). **Repoya commit edilmezler.** `.env.example` şablonu olarak kullanılmalıdır.

```env
# Uygulama
APP_ENV=development

# Veritabanı
DB_HOST=127.0.0.1
DB_PORT=3306
DB_USER=root
DB_PASS=password
DB_NAME=booking_saas

# Cloudflare R2 / S3 (opsiyonel)
CF_ACCOUNT_ID=
CF_ACCESS_KEY_ID=
CF_ACCESS_KEY_SECRET=
CF_BUCKET_NAME=
CF_ENDPOINT=
CF_PUBLIC_DEV_URL=
CDN_URL=
```

### Yükleme Sırası

1. `Constants.php` - Sabitler
2. `vendor/autoload.php` - Composer autoload
3. `System/Helpers/*.php` - Helper fonksiyonları (`get_env`, `import_config`, `import_env`)
4. `Container` oluşturulur, `Defines.php` providers yüklenir
5. `import_config('defines.app')` ile app config alınır
6. `import_env($config['env'])` ile `.env` dosyası yüklenir (`$_ENV` ve `putenv()`)

### `get_env()` ve `import_env()` Helperları

```php
// System/Helpers/env.php (tahmini implementasyon)
function get_env(string $key, $default = null) {
    return $_ENV[$key] ?? getenv($key) ?? $default;
}

function import_env(array $envMap): void {
    foreach ($envMap as $key => $envKey) {
        if (isset($_ENV[$envKey])) continue;
        $value = getenv($envKey);
        if ($value !== false) {
            $_ENV[$envKey] = $value;
            putenv("$envKey=$value");
        }
    }
}
```

**Defines.php'de env mapping:**

```php
'database' => [
    'connections' => [
        'primary' => [
            'db_host' => get_env('DB_HOST'),
            'db_pass' => get_env('DB_PASS'),
            // ...
        ]
    ]
]
```

---

## ⚙️ Development vs Production

### `APP_ENV` Değerleri

| Ortam       | `APP_ENV`     | `.env` Dosyası     | Hata Gösterimi                            |
| ----------- | ------------- | ------------------ | ----------------------------------------- |
| Development | `development` | `.env.development` | `E_ALL`, `display_errors=On`              |
| Production  | `production`  | `.env.production`  | `error_reporting=0`, `display_errors=Off` |

### `Public/index.php` Ayarları

```php
if (get_env('APP_ENV') === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
}
```

### Middleware Farkları

**Slash Middleware** (`App\Core\Middlewares\Slash`):

```php
public function handle(callable $next): mixed {
    if (get_env('APP_ENV') === 'development') {
        // Trailing slash redirect sadece development'ta
    }
    return $next();
}
```

### Swagger UI Asset Yolu

```php
// App/Modules/Swagger/SwaggerController
if (get_env('APP_ENV') === 'development') {
    $asset = '/swagger/';           // Public/swagger/ symlink veya direkt
} else {
    $asset = 'Public/swagger/';     // Production path
}
```

### Log Seviyesi

Production'da log dosyaları yine yazılır (Log provider her ortamda aktif), sadece ekrana basılmaz.

---

## 🔧 Container Providers (`defines.providers`)

Framework servisleri container'a singleton veya transient olarak kaydedilir:

```php
'providers' => [
    'benchmark'  => [System\Benchmark\Benchmark::class, true],
    'cache'      => [System\Cache\Cache::class, true],
    'cookie'     => [System\Cookie\Cookie::class, false],
    'curl'       => [System\Curl\Curl::class, false],
    'database'   => [System\Database\Database::class, true],
    'date'       => [System\Date\Date::class, false],
    'event'      => [System\Event\Event::class, true],
    'request'    => [System\Http\Request::class, true],
    'response'   => [System\Http\Response::class, true],
    'image'      => [System\Image\Image::class, false],
    'jwt'        => [System\Jwt\Jwt::class, true],
    'language'   => [System\Language\Language::class, true],
    'log'        => [System\Log\Log::class, true],
    'mail'       => [System\Mail\Mail::class, false],
    'pagination' => [System\Pagination\Pagination::class, false],
    'crypt'      => [System\Crypt\Crypt::class, true],
    'session'    => [System\Session\Session::class, true],
    'similarity' => [System\Text\Similarity::class, false],
    'upload'     => [System\Upload\Upload::class, false],
    'validation' => [System\Validation\Validation::class, false],
    'view'       => [System\View\View::class, true],
    'gate'       => [System\Gate\Gate::class, true],
    'error'      => [System\Exception\ExceptionHandler::class, true],
],
```

| Key          | Sınıf                          | Singleton | Kullanım                             |
| ------------ | ------------------------------ | --------- | ------------------------------------ |
| `database`   | `System\Database\Database`     | ✓         | `$container->get('database')`        |
| `request`    | `System\Http\Request`          | ✓         | Controller DI ile alır               |
| `response`   | `System\Http\Response`         | ✓         | Controller DI ile alır               |
| `jwt`        | `System\Jwt\Jwt`               | ✓         | Auth middleware, AuthService         |
| `cache`      | `System\Cache\Cache`           | ✓         | RateLimit middleware                 |
| `gate`       | `System\Gate\Gate`             | ✓         | Authorization checks                 |
| `upload`     | `System\Upload\Upload`         | ✗         | Service::upload()                    |
| `validation` | `System\Validation\Validation` | ✗         | Request::fill(), Service::validate() |

**Kendi Provider Eklemek:**

```php
// Defines.php providers array'ine ekleyin
'my_service' => [App\Services\MyService::class, true],

// Kullanım
$service = $container->get('my_service');
// veya Controller constructor'ında
public function __construct(protected MyService $service) {}
```

---

## 📂 Storage Dizinleri

`App/Storage/` runtime verileri için kullanılır (gitignore'edilmeli):

```
App/Storage/
├── Logs/        # Günlük log dosyaları (Log_YYYY-MM-DD.log)
├── Cache/       # File cache (namespace_key.cache)
└── curl_cookie.txt  # Curl cookie jar
```

**Permission:** `www-data:www-data` (Docker) veya web server user, `775` permission.

---

## 🐳 Docker ve Deployment

### `Dockerfile` Özet

```dockerfile
FROM php:8.4-apache

# PHP extensions: pdo_mysql, mbstring, exif, pcntl, bcmath, gd, zip, fileinfo, intl, opcache
# APCu: pecl install apcu

# Apache modules: rewrite, headers, expires, deflate

# Composer install --no-dev --optimize-autoloader

# Apache config: .docker/apache.conf -> /etc/apache2/sites-available/000-default.conf
# PHP config: .docker/php.ini -> /usr/local/etc/php/conf.d/99-app.ini

WORKDIR /var/www/html
COPY . .
RUN mkdir -p App/Storage/Logs App/Storage/Cache Public/upload \
    && chown -R www-data:www-data App/Storage Public/upload \
    && chmod -R 775 App/Storage Public/upload

EXPOSE 80
CMD ["apache2-foreground"]
```

### Apache Config (`.docker/apache.conf`)

- DocumentRoot: `/var/www/html/Public`
- DirectoryIndex: `index.php`
- AllowOverride All (`.htaccess` çalışması için)

### Production Deployment Checklist

- [ ] `APP_ENV=production`
- [ ] `.env.production` doğru değerlerle
- [ ] `composer install --no-dev --optimize-autoloader`
- [ ] `App/Storage/Logs`, `App/Storage/Cache`, `Public/upload` yazılabilir
- [ ] APCu etkin (rate limit, token lock için)
- [ ] OPcache etkin
- [ ] HTTPS terminasyonu (load balancer / reverse proxy)
- [ ] CORS headers (`defines.header`) production domain'e ayarlı
- [ ] Swagger basic auth şifreli (Defines.php `swagger` section)

---

## 🔑 Önemli Konfigürasyon Anahtarları Quick Reference

| Alan                 | Config Path                        | Açıklama                                       |
| -------------------- | ---------------------------------- | ---------------------------------------------- |
| JWT Secret           | `defines.jwt.secret`               | HS256 imzalama anahtarı (32+ char)             |
| JWT Expire           | `defines.jwt.expire.access`        | Access token süresi (saniye, default 3600)     |
| JWT Refresh Expire   | `defines.jwt.expire.refresh`       | Refresh token süresi (saniye, default 5184000) |
| Rate Limit           | `defines.rate_limit.enabled`       | true/false                                     |
| Rate Limit Max       | `defines.rate_limit.max_attempts`  | Dakika başına max istek                        |
| Rate Limit Custom    | `defines.rate_limit.custom_limits` | Endpoint bazlı limitler                        |
| Crypt Algorithm      | `defines.crypt.algorithm`          | PASSWORD_ARGON2ID                              |
| Session Lifetime     | `defines.session.lifetime`         | Saniye (default 3600)                          |
| Upload Provider      | `defines.upload.default`           | 'local' veya 'cloudflare'                      |
| Upload Allowed Types | `defines.upload.allowed_types`     | ['jpg','jpeg','png','gif','pdf','txt']         |
| Gate Provider        | `defines.gate.default`             | 'module' (ModuleHandler)                       |
| Log Path             | `defines.log.path`                 | 'Storage/Logs'                                 |
| Cache Path           | `defines.cache.path`               | 'Storage/Cache'                                |

---

> **Güvenlik Notu:** `Defines.php` içindeki `jwt.secret`, `crypt.secret`, `session.encryption_key`, `cookie.encryption_key` **production'da mutlaka değiştirilmelidir**. Bu değerler `.env` üzerinden yönetilmez, `Defines.php` doğrudan kaynak kodundadır. Production deployment öncesinde bu değerleri güçlü random stringlerle güncelleyin.
