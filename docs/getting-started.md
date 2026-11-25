# Başlangıç ve Temel Kullanım

Bu rehber, framework'ü tanımayan yeni bir geliştiricinin projeyi ayağa kaldırmasını ve ilk modülünü yazmasını sağlar.

## 📋 Gereksinimler

| Araç             | Sürüm                         |
| ---------------- | ----------------------------- |
| PHP              | >= 8.2.0                      |
| Composer         | 2.x                           |
| MySQL            | 8.4 LTS                       |
| Apache/Nginx     | mod_rewrite / rewrite rules   |
| APCu (opsiyonel) | Cache/RateLimit için önerilir |

**PHP Eklentileri:** pdo, pdo_mysql, mbstring, exif, pcntl, bcmath, gd, zip, fileinfo, intl, opcache, apcu

## 🚀 Kurulum

### 1. Bağımlılıkları Yükle

```bash
cd backend
composer install
```

### 2. Environment Dosyası

```bash
cp .env.development .env
# veya production için
cp .env.production .env
```

`.env` dosyası şu anahtarları içermelidir:

```env
APP_ENV=development
DB_HOST=127.0.0.1
DB_PORT=3306
DB_USER=root
DB_PASS=password
DB_NAME=booking_saas
CF_ACCOUNT_ID=          # Cloudflare R2 kullanıyorsanız
CF_ACCESS_KEY_ID=
CF_ACCESS_KEY_SECRET=
CF_BUCKET_NAME=
CF_ENDPOINT=
CF_PUBLIC_DEV_URL=
CDN_URL=
```

### 3. Veritabanı Oluştur

```sql
CREATE DATABASE booking_saas CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
```

### 4. Migration ve Seed

```bash
# Migration'ları çalıştır
php cli migrate

# Seed verilerini yükle (roller, izinler, admin kullanıcı)
php cli seed
```

### 5. Web Sunucusu

**Development (PHP built-in):**

```bash
php -S localhost:8000 -t Public
```

**Production (Apache/Nginx):**

- DocumentRoot: `backend/Public`
- Rewrite rules: `Public/.htaccess` (Apache) veya `subdir.htaccess` (subdirectory deployment)

## 🎯 İlk Modül Oluşturma

### Adım 1: Modül Klasörü

```bash
mkdir -p App/Modules/Example
```

### Adım 2: Migration

`App/Migrations/0000_00_00_000_example.php`:

```php
<?php
declare(strict_types=1);

use App\Core\Abstracts\Migration;

class example extends Migration {
    public function up(): void {
        $this->database->query("CREATE TABLE IF NOT EXISTS `example` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `title` VARCHAR(255) NOT NULL,
            `content` TEXT NULL,
            `is_active` BOOLEAN NOT NULL DEFAULT 1,
            {$this->defaults()}
        )");
    }

    public function down(): void {
        $this->database->query("DROP TABLE IF EXISTS `example`");
    }
}
```

Migration çalıştır:

```bash
php cli migrate
```

### Adım 3: Repository

`App/Modules/Example/ExampleRepository.php`:

```php
<?php
declare(strict_types=1);

namespace App\Modules\Example;

use System\Database\Database;
use App\Core\Abstracts\Repository;

class ExampleRepository extends Repository {
    public function __construct(
        protected Database $database,
        protected string $table = 'example'
    ) {}

    // Özel query'ler için metodlar ekleyin
    public function findActive(): array {
        return $this->findAllBy(['is_active' => 1], ['sort_order' => 'ASC']);
    }
}
```

### Adım 4: Request (Input Validation)

`App/Modules/Example/ExampleRequest.php`:

```php
<?php
declare(strict_types=1);

namespace App\Modules\Example;

use App\Core\Abstracts\Resource;

class ExampleRequest extends Resource {
    public ?int $id;
    public string $title;
    public ?string $content;
    public int $is_active;

    protected function rules(): array {
        return [
            'title'      => ['required', 'string', 'max:255'],
            'content'    => ['nullable', 'string'],
            'is_active'  => ['required', 'numeric', 'between:0,1'],
        ];
    }

    protected function labels(): array {
        return [
            'title'     => 'Başlık',
            'content'   => 'İçerik',
            'is_active' => 'Aktiflik',
        ];
    }

    protected function messages(): array {
        return [
            'required' => 'Geçerli bir :label giriniz.',
            'max'      => ':label en fazla :max karakter olmalıdır.',
        ];
    }
}
```

### Adım 5: Response (Output Mapping)

`App/Modules/Example/ExampleResponse.php`:

```php
<?php
declare(strict_types=1);

namespace App\Modules\Example;

use App\Core\Abstracts\{AuditTrait, Resource};

class ExampleResponse extends Resource {
    use AuditTrait;  // created_at, updated_at, deleted_at, created_by, updated_by, deleted_by

    public int $id;
    public string $title;
    public ?string $content;
    public int $is_active;
}
```

### Adım 6: Service (Business Logic)

`App/Modules/Example/ExampleService.php`:

```php
<?php
declare(strict_types=1);

namespace App\Modules\Example;

use System\Exception\SystemException;
use App\Core\Abstracts\Service;
use App\Modules\Example\{ExampleRepository, ExampleRequest};

class ExampleService extends Service {
    public function __construct(
        protected ExampleRepository $repository
    ) {}

    public function getAll(): array {
        return $this->repository->findAll();
    }

    public function getOne(int $id): array {
        $result = $this->repository->findOne($id);
        if (empty($result)) {
            throw new SystemException('Record not found', 404);
        }
        return $result;
    }

    public function create(ExampleRequest $request): array {
        return $this->repository->transaction(function () use ($request): array {
            $this->check($this->repository, ['title' => $request->title], $request);

            $create = $this->repository->create([
                'title'     => $request->title,
                'content'   => $request->content,
                'is_active' => $request->is_active,
            ]);

            return $this->repository->findOne($create->lastInsertId());
        });
    }

    public function update(ExampleRequest $request): array {
        return $this->repository->transaction(function () use ($request): array {
            $this->check($this->repository, ['title' => $request->title], $request, false);

            $this->repository->update(
                $request->filter([
                    'title'     => $request->title,
                    'content'   => $request->content,
                    'is_active' => $request->is_active,
                ]),
                ['id' => $request->id]
            );

            return $this->getOne($request->id);
        });
    }

    public function delete(int $id): bool {
        return $this->repository->transaction(function () use ($id): bool {
            $this->repository->softDelete(['id' => $id, 'deleted_at' => ['IS NULL']]);
            return true;
        });
    }
}
```

### Adım 7: Controller (HTTP Layer)

`App/Modules/Example/ExampleController.php`:

```php
<?php
declare(strict_types=1);

namespace App\Modules\Example;

use System\Http\{Request, Response};
use App\Core\Abstracts\Controller;
use App\Modules\Example\{ExampleRequest, ExampleResponse, ExampleService};

/**
 * @OA\Tag(name="Example", description="Örnek işlemler")
 */
class ExampleController extends Controller {
    public function __construct(
        protected Response $response,
        protected Request $request,
        protected ExampleService $service
    ) {}

    /**
     * @OA\Get(tags={"Example"}, path="/example", summary="Listele",
     *    @OA\Response(response=200, description="Success")
     * )
     */
    public function getAll(): void {
        $result = $this->service->getAll();
        $list = array_map(fn($item) => (new ExampleResponse())->map($item), $result);
        $this->response->json($list);
    }

    /**
     * @OA\Get(tags={"Example"}, path="/example/{id}", summary="Detay",
     *    @OA\Response(response=200, description="Success"),
     *    @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer"))
     * )
     */
    public function getById(int $id): void {
        $result = $this->service->getOne($id);
        $response = (new ExampleResponse())->map($result);
        $this->response->json($response);
    }

    /**
     * @OA\Post(tags={"Example"}, path="/example", summary="Oluştur",
     *    @OA\Response(response=201, description="Created"),
     *    @OA\RequestBody(required=true, @OA\JsonContent(
     *       required={"title", "is_active"},
     *       @OA\Property(property="title", type="string", example="Örnek Başlık"),
     *       @OA\Property(property="content", type="string", example="İçerik"),
     *       @OA\Property(property="is_active", type="integer", example=1)
     *    ))
     * )
     */
    public function create(): void {
        $json = $this->request->json();
        $request = new ExampleRequest();
        $request->fill($json);

        $result = $this->service->create($request);
        $response = (new ExampleResponse())->map($result);
        $this->response->json($response, 201);
    }

    /**
     * @OA\Put(tags={"Example"}, path="/example", summary="Güncelle",
     *    @OA\Response(response=200, description="Success"),
     *    @OA\RequestBody(required=true, @OA\JsonContent(
     *       required={"id", "title", "is_active"},
     *       @OA\Property(property="id", type="integer", example=1),
     *       @OA\Property(property="title", type="string", example="Güncellenmiş Başlık"),
     *       @OA\Property(property="content", type="string", example="Yeni içerik"),
     *       @OA\Property(property="is_active", type="integer", example=1)
     *    ))
     * )
     */
    public function update(): void {
        $json = $this->request->json();
        $request = new ExampleRequest();
        $request->fill($json);

        $result = $this->service->update($request);
        $response = (new ExampleResponse())->map($result);
        $this->response->json($response);
    }

    /**
     * @OA\Delete(tags={"Example"}, path="/example/{id}", summary="Sil",
     *    @OA\Response(response=200, description="Success"),
     *    @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer"))
     * )
     */
    public function delete(int $id): void {
        $result = $this->service->delete($id);
        $this->response->json($result);
    }
}
```

### Adım 8: Route Tanımı

`App/Routes/Routes.php` dosyasına ekleyin:

```php
use App\Modules\Example\ExampleController;

// Example routes
$router->prefix('api/v1/example')->middleware([Auth::class])->group(function () use ($router) {
    $router->get('/', [ExampleController::class, 'getAll']);
    $router->get('/{id}', [ExampleController::class, 'getById'])->where(['id' => '([0-9]+)']);
    $router->post('/', [ExampleController::class, 'create']);
    $router->put('/', [ExampleController::class, 'update']);
    $router->delete('/{id}', [ExampleController::class, 'delete'])->where(['id' => '([0-9]+)']);
});
```

### Adım 9: Swagger Dokümantasyonu

Controller metodlarındaki `@OA\*` annotation'ları Swagger UI için kullanılır.

```bash
# Swagger JSON'ı yeniden üretmek için
# App/Modules/Swagger/SwaggerController::json() endpoint'ini çağırın
# veya tarayıcıdan /api/swagger/json adresine gidin
```

## ✅ Test Etme

```bash
# Listele
curl -H "Authorization: Bearer <access_token>" http://localhost:8000/api/v1/example

# Oluştur
curl -X POST -H "Authorization: Bearer <access_token>" \
  -H "Content-Type: application/json" \
  -d '{"title":"Test","content":"İçerik","is_active":1}' \
  http://localhost:8000/api/v1/example
```

## 🔍 Önemli Noktalar

| Konu                    | Kural                                                                                       |
| ----------------------- | ------------------------------------------------------------------------------------------- |
| **Namespace**           | `App\Modules\ModuleName`                                                                    |
| **Tablo adı**           | `snake_case` (örn. `product_category`)                                                      |
| **Migration sınıf adı** | Tablo adı ile aynı (lowercase)                                                              |
| **Soft delete**         | `Repository::findAll()` zaten `deleted_at IS NULL` filtresi uygular                         |
| **Audit fields**        | Migration'da `$this->defaults()` ile eklenir                                                |
| **Validation**          | `Request::fill()` otomatik validasyon yapar, hata durumunda `SystemException(400)` fırlatır |
| **DI**                  | Controller constructor'ında type-hint ile alınır                                            |
| **Transaction**         | `Repository::transaction(callback)` ile güvenli transaction                                 |
| **Response**            | `Response::json(data, status)` - controller `void` döndürür                                 |

## 🐛 Sık Karşılaşılan Hatalar

| Hata                           | Sebep                            | Çözüm                                                       |
| ------------------------------ | -------------------------------- | ----------------------------------------------------------- |
| `Class "System\..." not found` | `composer install` yapılmamış    | `composer install` çalıştırın                               |
| `Database connection failed`   | `.env` yanlış veya DB down       | `.env` kontrol edin, MySQL çalışıyor mu?                    |
| `Validation failed`            | Request verisi rules'a uymuyor   | Request payload'ını `rules()` ile karşılaştırın             |
| `401 Unauthorized`             | JWT token geçersiz/süresi dolmuş | `/api/v1/auth/refresh` ile yenileyin veya tekrar login olun |
| `403 Forbidden`                | Yetki yok (policy/role)          | Kullanıcının ilgili permission/role'u var mı kontrol edin   |
| `Route not found`              | Route tanımı eksik               | `App/Routes/Routes.php` kontrol edin                        |

---

> **İpucu:** Mevcut modüllerden (`Product`, `Category`, `User`) kopyalayıp uyarlamak en hızlı yoldur. Framework'ün pattern'i tutarlıdır.
