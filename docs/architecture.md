# Framework Mimarisi ve Çalışma Akışı

Bu belge framework'ün çekirdek mimarisini, katmanlarını ve HTTP request'in framework içinde izlediği yolu açıklar.

## 🏗️ Katmanlar ve Sorumluluklar

```
┌─────────────────────────────────────────────────────────────────┐
│                        HTTP REQUEST                              │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│  Public/index.php (Bootstrap / Front Controller)                │
│  • Constants, Autoload, Helpers                                  │
│  • Container oluşturma & Provider kayıt                          │
│  • Exception/Error handler                                      │
│  • Config & Env yükleme                                         │
│  • Router oluşturma & Route yükleme                             │
│  • Router->run()                                                │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│  System\Router\Router                                           │
│  • Route eşleştirme (prefix, middleware, where, group)          │
│  • Middleware pipeline çalıştırma                               │
│  • Controller instantiate (DI ile) & method çağırma             │
└─────────────────────────────────────────────────────────────────┘
                              │
              ┌───────────────┼───────────────┐
              ▼               ▼               ▼
┌──────────────────┐ ┌────────────────┐ ┌──────────────┐
│   Middleware     │ │  Controller    │ │   Service    │
│  (Auth, RateLimit│ │  (Request/     │ │  (Business   │
│   Security,...)  │ │   Response)    │ │   Logic)     │
└──────────────────┘ └────────────────┘ └──────────────┘
                                                │
                                                ▼
                                         ┌──────────────┐
                                         │ Repository   │
                                         │ (Data Access)│
                                         └──────────────┘
                                                │
                                                ▼
                                         ┌──────────────┐
                                         │ System\Data- │
                                         │ base\Database│
                                         └──────────────┘
```

### Katman Ayrımı

| Katman                  | Dizini                          | Sorumluluk                                                                                                                                                                              | Değiştirilebilir mi?                         |
| ----------------------- | ------------------------------- | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | -------------------------------------------- |
| **Framework Çekirdeği** | `System/`                       | Container, Router, Database, Http, Jwt, Cache, Log, Upload, Validation, Gate, Event, Session, Crypt, Mail, Image, Pagination, Language, Date, Curl, Cookie, Benchmark, ExceptionHandler | **Hayır** (Framework sahibi)                 |
| **Uygulama Çekirdeği**  | `App/Core/`                     | Abstract base sınıflar (Controller, Service, Repository, Resource, Policy, Collection, Migration, Seeder), Middlewares, Handlers, Listeners, Enums, Exceptions                          | **Evet** (Proje gerektirse genişletilebilir) |
| **Feature Modülleri**   | `App/Modules/`                  | Domain logic: Controller, Service, Repository, Request, Response, Policy                                                                                                                | **Evet** (Ana geliştirme alanı)              |
| **Konfigürasyon**       | `App/Config/`                   | Constants, Defines, Authorization, Approval                                                                                                                                             | **Evet**                                     |
| **Routing**             | `App/Routes/`                   | Route tanımları                                                                                                                                                                         | **Evet**                                     |
| **Veritabanı**          | `App/Migrations/`, `App/Seeds/` | Schema ve seed verileri                                                                                                                                                                 | **Evet**                                     |

## 🔄 HTTP Request Yaşam Döngüsü

### 1. Bootstrap (`Public/index.php`)

```php
// 1. Sabitler ve autoload
require_once dirname(__DIR__) . '/App/Config/Constants.php';
require_once dirname(__DIR__) . '/vendor/autoload.php';
foreach (glob(dirname(__DIR__) . '/System/Helpers/*.php') as $filename) {
    require_once $filename;
}

// 2. Container & Providers
$container = new Container();
$container->register();  // Defines.php 'providers' bölümünden kayıt

// 3. Exception / Error Handler
set_exception_handler([$container->get('error'), 'handleException']);
set_error_handler([$container->get('error'), 'handleError']);

// 4. Error Reporting (APP_ENV'e göre)
if (get_env('APP_ENV') === 'development') { ... }

// 5. Config & Env
$config = import_config('defines.app');
import_env($config['env']);

// 6. Router
$router = new Router($container);
foreach (glob(APP_DIR . 'Routes/*.php') as $route) {
    require_once $route;
}
$router->run();
```

### 2. Router (`System\Router\Router`)

- `prefix()`: URL prefix'i (örn. `api/v1/auth`)
- `middleware()`: Middleware sınıfları dizisi
- `group()`: Closure içine route grubu
- `where()`: Parametre regex kısıtlaması (örn. `['id' => '([0-9]+)']`)
- HTTP verb metodları: `get()`, `post()`, `put()`, `patch()`, `delete()`

### 3. Middleware Pipeline

Her middleware `handle(callable $next): mixed` imzasına sahiptir:

```php
class Auth {
    public function handle(callable $next): mixed {
        $token = $this->request->bearer();
        $decode = $this->jwt->parseToken($token);
        $this->request->setUser($decode);  // Request'e user verisi ekler
        return $next();  // Sonraki middleware veya controller'a geç
    }
}
```

**Varsayılan middlewares** (Defines.php):

- `Security` - Robot tespiti, URI validasyonu
- `Slash` - Trailing slash redirect (sadece development)

**Route-level middlewares** (Routes.php):

- `Auth` - JWT doğrulama
- `RateLimit` - IP bazlı rate limiting

### 4. Controller Çalıştırma

Router eşleşen route'un controller/method'unu çözümler:

```php
// Controller constructor DI ile alınır
public function __construct(
    protected Response $response,
    protected Request $request,
    protected ProductService $service
) {}

// Method çağrılır
public function getAll(): void {
    $result = $this->service->getAll();
    $this->response->json($result);
}
```

### 5. Response Üretimi

- `System\Http\Response::json(mixed $data, int $status = 200)` - JSON response
- Controller `void` döndürür, response doğrudan gönderilir

## 🧩 Framework Çekirdeği (System\) Bileşenleri

`composer.json` autoload'da `System\\` namespace'i `System/` dizinine eşlenir. Bu sınıflar **framework çekirdeğidir** ve `vendor/` gibi davranır (proje kodunda değiştirilmezler).

| Sınıf (Provider Key) | Açıklama                                                                         | Singleton |
| -------------------- | -------------------------------------------------------------------------------- | --------- |
| `Container`          | DI container, provider kayıt/çözümleme                                           | -         |
| `Router`             | Routing, middleware pipeline, controller dispatch                                | -         |
| `Database`           | PDO wrapper, query builder, transaction                                          | ✓         |
| `Request`            | HTTP request verisi (json, files, bearer, ip, ua, lang)                          | ✓         |
| `Response`           | HTTP response (json, header, status)                                             | ✓         |
| `Jwt`                | JWT create/parse/validate (HS256)                                                | ✓         |
| `Cache`              | APCu / File cache abstraction                                                    | ✓         |
| `Log`                | File-based logging (günlük dosyalar)                                             | ✓         |
| `Session`            | PHP session wrapper (encrypted cookie)                                           | ✓         |
| `Crypt`              | Password hash (argon2id) + AES şifreleme                                         | ✓         |
| `Validation`         | Rule-based validation (required, email, numeric, date, between, array, regex...) | -         |
| `Upload`             | File upload (local / Cloudflare R2 providers)                                    | -         |
| `Gate`               | Authorization gate (permission/role check)                                       | ✓         |
| `Event`              | Event dispatcher (listener pattern)                                              | ✓         |
| `Mail`               | PHPMailer wrapper (SMTP)                                                         | -         |
| `Image`              | GD tabanlı image manipulation                                                    | -         |
| `Pagination`         | Sayfalama helper                                                                 | -         |
| `Language`           | IntlDateFormatter tabanlı locale/date formatting                                 | ✓         |
| `Date`               | DateTime wrapper (format, modify, timezone)                                      | -         |
| `Curl`               | HTTP client wrapper                                                              | -         |
| `Cookie`             | Encrypted cookie helper                                                          | -         |
| `Similarity`         | Text similarity (Levenshtein, vb.)                                               | -         |
| `Benchmark`          | Microtime-based performance ölçümü                                               | ✓         |
| `ExceptionHandler`   | Global exception/error handling + logging                                        | ✓         |
| `View`               | Basit PHP template engine (include-based)                                        | ✓         |

## 🔗 App\Core\Abstracts - Uygulama Base Sınıfları

Bu sınıflar `App/` katmanında yer alır ve **genişletilebilir/override edilebilir**.

### Controller

```php
abstract class Controller {
    protected Request $request;
    protected Response $response;

    final protected function params(?string $param = null): array|int|string {
        // lang ve curr query param'larından language_id, currency_id döndürür
    }
}
```

### Service

```php
abstract class Service {
    protected Upload $upload;
    protected Validation $validation;

    protected function check(Repository $repo, array $fields, Resource $resource, bool $create = true): void
    public function validate(array $data, array $rules, ?array $labels = [], ?array $messages = []): void
    protected function translate(Repository $repo, array $fields, array $where, array $translations, string $table): void
    protected function upload(?array $files, ?string $dir = null): array
    protected function unlink(string|array|null $files = null): bool
}
```

### Repository

```php
abstract class Repository {
    protected Database $database;
    protected string $table;

    public function findAll(): array
    public function findAllBy(?array $where = [], ?array $orderBy = null): array
    public function findOne(int $id): ?array
    public function findBy(array $where, ?string $table = null): ?array
    public function create(array $fields, ?string $table = null): Database
    public function update(array $fields, array $where, ?string $table = null): Database
    public function hardDelete(array $where, ?string $table = null): Database
    public function softDelete(array $where, ?string $table = null): Database
    final public function transaction(callable $callback): mixed
}
```

### Resource (Request/Response base)

```php
abstract class Resource {
    final public function property(?string $key = null): mixed
    final public function filter(array $data): array
    final public function fill(array $data): void        // Validation + assignment
    final public function map(array $data): self         // Reflection + type casting + nested Resource/Collection
    protected function rules(): array                    // Validation rules
    protected function labels(): array                   // Field labels
    protected function messages(): array                 // Custom error messages
}
```

### Collection

```php
final class Collection implements IteratorAggregate, JsonSerializable {
    public function __construct(private string $collectionClass)
    public function setItem(array $data): void           // Array -> Collection<Resource>
}
```

### Policy

```php
abstract class Policy {
    public function before(?array $user, string $ability): ?bool  // Super-admin bypass
    public function after(?array $user, string $ability, bool $result): ?bool
    public function viewAny(?array $user): bool
    public function view(?array $user, mixed $model = null): bool
    public function create(?array $user): bool
    public function update(?array $user, mixed $model = null): bool
    public function delete(?array $user, mixed $model = null): bool

    protected function hasPermission(?array $user, string $permission): bool
    protected function hasAnyPermission(?array $user, array $permissions): bool
    protected function hasRole(?array $user, string $role): bool
    // ... hasAllPermission, hasAnyRole, hasAllRole
}
```

### Migration / Seeder

```php
abstract class Migration {
    protected Database $database;
    abstract public function up(): void;
    abstract public function down(): void;
    protected function defaults(): string  // Audit + soft delete fields
}

abstract class Seeder {
    protected Database $database;
    abstract public function run(): void;
}
```

## 📦 Modül Yapısı (App\Modules\*)

Her feature modülü şu yapıda olur:

```
App/Modules/Product/
├── ProductController.php    # HTTP layer, DI ile Service alır
├── ProductService.php       # Business logic, Repository kullanır
├── ProductRepository.php    # Data access, Database query builder
├── ProductRequest.php       # Input validation (extends Resource)
├── ProductResponse.php      # Output mapping (extends Resource, Collection içerebilir)
└── ProductPolicy.php        # Authorization (extends Policy) - opsiyonel
```

**Örnek akış:**

```
Route -> Controller::getAll()
    -> Service::getAll()
        -> Repository::findAll()
            -> Database::table()->select()->where()->execute()->fetchAll()
    -> Response::map() (Resource::map ile nested object/collection oluşturur)
    -> Response::json()
```

## 🛠️ Helper Fonksiyonları (System/Helpers/)

`Public/index.php` ve `cli` dosyasında `glob(SYSTEM_DIR . 'Helpers/*.php')` ile yüklenir. Yaygın kullanılanlar:

| Fonksiyon                               | Açıklama                                                                      |
| --------------------------------------- | ----------------------------------------------------------------------------- |
| `get_env(string $key, $default = null)` | `$_ENV` veya `getenv()` ile env okur                                          |
| `import_config(string $path)`           | `Defines.php` içinden dot notation ile config alır (`defines.app.migrations`) |
| `import_env(array $envMap)`             | Config'den env key'leri `$_ENV`'e set eder                                    |
| `uri_get()`                             | Current route path (query string olmadan)                                     |

## ⚙️ Container ve Dependency Injection

`System\Container\Container` basit bir PSR-11 uyumlu container'dır.

**Provider Kaydı** (Defines.php `providers` array):

```php
'providers' => [
    'database' => [System\Database\Database::class, true],   // [class, singleton]
    'request'  => [System\Http\Request::class, true],
    // ...
]
```

**Çözümleme:**

```php
$container->get('database');        // Singleton ise aynı instance
$container->resolveClass(ClassName::class);  // Constructor DI ile instantiate
```

Controller constructor'ında type-hint ile DI alınır:

```php
public function __construct(
    protected Response $response,      // 'response' provider
    protected Request $request,        // 'request' provider
    protected ProductService $service  // Container resolveClass ile
) {}
```

## 🔄 Event / Listener Sistemi

`System\Event\Event` singleton provider olarak kayıtlıdır.

**Defines.php listeners:**

```php
'listeners' => [
    'sampleEvent' => [App\Core\Listeners\SampleListener::class]
]
```

**Kullanım:**

```php
$event = $container->get('event');
$event->listen('event.name', fn($data) => ...);
$event->dispatch('event.name', $data);
```

## 📝 Hata Yönetimi ve Loglama

`System\Exception\ExceptionHandler` (provider: `error`):

- `handleException(Throwable $e)` - Uncaught exception
- `handleError(int $code, string $message, string $file, int $line)` - PHP error

**Loglama** (`System\Log\Log`):

- Günlük dosyalar: `Storage/Logs/Log_Y-m-d.log`
- Format: `H:i:s [LEVEL] message`
- Seviyeler: emergency, alert, critical, error, warning, notice, info, debug

**SystemException** - Framework'ün temel exception sınıfı (HTTP status code ile):

```php
throw new SystemException('Record not found', 404);
throw new SystemException(json_encode($validation->errors()), 400);
```

---

> **Önemli Ayrım:** `System\*` sınıfları **framework çekirdeğidir**, `App\Core\Abstracts\*` sınıfları **uygulama base sınıflarıdır**. Yeni modül yazarken `App\Core\Abstracts` sınıflarından extend edilir; `System\*` sınıfları DI ile alınır ve doğrudan extend edilmez.
