# CLI ve Yardımcı Araçlar

Bu belge framework'ün CLI entry point'ini, mevcut komutları ve geliştirici araçlarını açıklar.

## 🖥️ CLI Entry Point (`backend/cli`)

```bash
#!/usr/bin/php
<?php
declare(strict_types=1);

use System\Cli\Cli;

if (php_sapi_name() !== 'cli') {
    exit('CLI only');
}
chdir(__DIR__);

require_once __DIR__ . "/App/Config/Constants.php";
require_once __DIR__ . "/vendor/autoload.php";
foreach (glob(SYSTEM_DIR . "Helpers/*.php") as $filename) {
    require_once $filename;
}

$params = array_slice($argv, 1);
$cli = new Cli();
echo $cli->run($params);
```

**Kullanım:**

```bash
cd backend
php cli <command> [args...]
```

## 📋 Mevcut Komutlar

`System\Cli\Cli` sınıfında tanımlanan komutlar:

### 1. `migrate` - Veritabanı Migration Çalıştırma

```bash
php cli migrate
```

**Ne yapar:**

- `App/Migrations/` dizinindeki tüm `.php` dosyalarını tarar
- Sınıf adı (lowercase tablo adı) ile `up()` metodunu çağırır
- Çalıştırılmış migration'ları takip etmek için tablo/yonetim **yok** - her çalışmada tüm `up()` tekrar denenir (idempotent `CREATE TABLE IF NOT EXISTS` ile)

**Çıktı örneği:**

```
Running migrations...
· product table created/verified
· category table created/verified
· brand table created/verified
...
Migrations completed.
```

### 2. `seed` - Seed Verilerini Yükleme

```bash
php cli seed
```

**Ne yapar:**

- `App/Seeds/` dizinindeki dosyaları isim sırasına göre (01*, 02*...) çalıştırır
- Her seeder sınıfının `run()` metodunu çağırır
- Try-catch ile hata yakalar, devam eder

**Çıktı örneği:**

```
Running seeds...
· Permissions seeded
· Roles seeded
· Role super-admin assigned to User #1
· Approval templates seeded
Seeds completed.
```

### 3. Diğer Komutlar

`System\Cli\Cli` sınıfını inceleyerek mevcut komutları görebilirsiniz. Framework şu an için temel `migrate` ve `seed` komutlarını sağlar.

## 🛠️ Geliştirici Araçları

### 1. Swagger/OpenAPI Dokümantasyonu

**Controller Annotation'ları:**

```php
/**
 * @OA\Get(tags={"Product"}, path="/product", summary="Ürün listesi",
 *    @OA\Response(response=200, description="Success")
 * )
 */
public function getAll(): void { ... }
```

**JSON Üretimi:**

```bash
# Tarayıcıdan
curl http://localhost:8000/api/swagger/json

# Veya SwaggerController::json() endpoint'ini çağırın
```

**Swagger UI:**

```
http://localhost:8000/api/swagger/
```

**Desteklenen Annotation'lar (zircote/swagger-php):**

- `@OA\Info`, `@OA\Server`, `@OA\Tag`
- `@OA\Get`, `@OA\Post`, `@OA\Put`, `@OA\Patch`, `@OA\Delete`
- `@OA\Response`, `@OA\RequestBody`, `@OA\Parameter`
- `@OA\Schema`, `@OA\Property`, `@OA\Items`
- `@OA\SecurityScheme` (BearerAuth)

### 2. Helper Fonksiyonları (System/Helpers/)

`Public/index.php` ve `cli` başlangıcında `glob(SYSTEM_DIR . 'Helpers/*.php')` ile yüklenir.

**Yaygın Helperlar:**

| Fonksiyon                               | Açıklama                                           |
| --------------------------------------- | -------------------------------------------------- |
| `get_env(string $key, $default = null)` | `$_ENV` veya `getenv()` ile env okur               |
| `import_config(string $path)`           | `Defines.php` içinden dot notation ile config alır |
| `import_env(array $envMap)`             | Config'den env key'leri `$_ENV`'e set eder         |
| `uri_get()`                             | Current route path (query string olmadan)          |
| `asset(string $path)`                   | Asset URL üretimi (geliştirme/production farkı)    |
| `old(string $key, $default = null)`     | Eski form verisi (session flash)                   |
| `csrf_token()`                          | CSRF token üretimi                                 |
| `csrf_field()`                          | CSRF hidden input HTML                             |

### 3. Log Görüntüleme

```bash
# Development log'ları
tail -f App/Storage/Logs/Log_$(date +%Y-%m-%d).log

# Tüm log'lar
ls App/Storage/Logs/
```

**Log Formatı:**

```
H:i:s [LEVEL] message
# Örnek:
14:32:15 [INFO] User login: admin@example.com
14:32:16 [ERROR] SQLSTATE[23000]: Integrity constraint violation
```

### 4. Cache Temizleme

```bash
# File cache temizleme
rm -rf App/Storage/Cache/*

# APCu cache temizleme (CLI'den)
php -r "apcu_clear_cache();"
```

### 5. Composer Scripts

`composer.json` için önerilen scriptler (mevcut değil, eklenebilir):

```json
{
  "scripts": {
    "migrate": "php cli migrate",
    "seed": "php cli seed",
    "test": "php vendor/bin/phpunit",
    "cs-fix": "php vendor/bin/php-cs-fixer fix",
    "swagger": "php -r \"require 'vendor/autoload.php'; (new App\\Modules\\Swagger\\SwaggerController())->json();\""
  }
}
```

**Kullanım:**

```bash
composer migrate
composer seed
```

---

## 🐛 Debug İpuçları

### 1. Request/Response Debug

```php
// Controller'da
$json = $this->request->json();
error_log('REQUEST: ' . print_r($json, true));

// Response öncesi
error_log('RESPONSE: ' . print_r($data, true));
$this->response->json($data);
```

### 2. SQL Debug

```php
// Repository'de
$sql = $this->database->getLastQuery();  // Eğer Database sınıfında varsa
error_log('SQL: ' . $sql);

// Veya prepared statement + params logla
```

### 3. Container Debug

```php
// Hangi provider'lar kayıtlı?
$container = new Container();
$container->register();
// Reflection ile providers property'ini inceleyin
```

### 4. Xdebug / IDE Debug

`docker-php-ext-install xdebug` ve `php.ini`:

```ini
zend_extension=xdebug
xdebug.mode=debug
xdebug.client_host=host.docker.internal
xdebug.client_port=9003
xdebug.start_with_request=yes
```

---

## 📦 Yeni CLI Komutu Ekleme

`System\Cli\Cli` sınıfını genişletin:

```php
// System/Cli/Cli.php (framework çekirdeği - dikkatli değiştirin)
class Cli {
    public function run(array $params): string {
        $command = $params[0] ?? 'help';

        return match ($command) {
            'migrate' => $this->migrate(),
            'seed'    => $this->seed(),
            'custom'  => $this->customCommand($params),
            default   => $this->help(),
        };
    }

    private function customCommand(array $params): string {
        // Yeni komut implementasyonu
        return "Custom command executed\n";
    }
}
```

**Veya uygulama seviyesinde ayrı CLI script:**

```bash
# backend/cli-custom
#!/usr/bin/php
<?php
require_once __DIR__ . '/App/Config/Constants.php';
require_once __DIR__ . '/vendor/autoload.php';

$container = new \System\Container\Container();
$container->register();

// Custom logic
$service = $container->resolveClass(App\Modules\Product\ProductService::class);
$result = $service->getAll();
print_r($result);
```

---

## 🔧 Yaygın Sorunlar ve Çözümler

| Sorun                                    | Neden                      | Çözüm                                                                 |
| ---------------------------------------- | -------------------------- | --------------------------------------------------------------------- |
| `php cli migrate` - "Class not found"    | Autoload yok               | `composer install` veya `composer dump-autoload`                      |
| `php cli migrate` - DB connection failed | `.env` yanlış              | `.env` dosyasını kontrol edin, MySQL çalışıyor mu?                    |
| `php cli seed` - Duplicate entry         | Seed zaten çalışmış        | Seed idempotent değil, manuel temizleyin veya `IF NOT EXISTS` ekleyin |
| Swagger JSON boş                         | Annotation parse edilemedi | `@OA\*` syntax'ını kontrol edin, `zircote/swagger-php` versiyonu      |
| APCu fonksiyonları bulunamıyor           | APCu extension yok         | `pecl install apcu` ve `docker-php-ext-enable apcu`                   |

---

> **Not:** `System\Cli\Cli` framework çekirdeğindedir. Yeni komut eklerken mevcut pattern'i bozmamaya dikkat edin. Uygulama özelinde komutlar için ayrı script yazmayı tercih edin.
