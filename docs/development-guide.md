# Geliştirme Rehberi

Bu belge, framework'ün sunduğu pattern'leri kullanarak günlük geliştirme işlemlerini nasıl yapacağınızı açıklar.

## 📐 Modül Geliştirme Pattern'i

Her modül **5 temel sınıf**dan oluşur:

```
App/Modules/ModulAdi/
├── ModulAdiController.php    # HTTP Layer (Request/Response)
├── ModulAdiService.php       # Business Logic
├── ModulAdiRepository.php    # Data Access
├── ModulAdiRequest.php       # Input Validation (extends Resource)
└── ModulAdiResponse.php      # Output Mapping (extends Resource)
```

**Opsiyonel:** `ModulAdiPolicy.php` (Authorization için)

---

## 🛣️ Route Tanımlama

`App/Routes/Routes.php` dosyasında yapılır.

### Temel Sözdizimi

```php
use App\Modules\Product\ProductController;
use App\Core\Middlewares\{Auth, RateLimit};

/** @var System\Router\Router $router */

// Public route (auth yok, rate limit var)
$router->prefix('api/v1/auth')->middleware([RateLimit::class])->group(function () use ($router) {
    $router->post('/login', [AuthController::class, 'login']);
    $router->post('/refresh', [AuthController::class, 'refresh']);
});

// Protected route (auth + rate limit)
$router->prefix('api/v1/product')->middleware([Auth::class])->group(function () use ($router) {
    $router->get('/', [ProductController::class, 'getAll']);
    $router->post('/', [ProductController::class, 'create']);
    $router->put('/', [ProductController::class, 'update']);
    $router->delete('/{id}', [ProductController::class, 'delete'])->where(['id' => '([0-9]+)']);
    $router->get('/{id}', [ProductController::class, 'getById'])->where(['id' => '([0-9]+)']);
});
```

### Router Metotları

| Metot                        | Açıklama                                             |
| ---------------------------- | ---------------------------------------------------- | --- | ----- | ------------------------------------- | ----------------- |
| `prefix(string $prefix)`     | URL prefix'i ekler (`api/v1/product`)                |
| `middleware(array $classes)` | Middleware sınıfları dizisi                          |
| `group(callable $callback)`  | Route grubu (closure içinde route'lar)               |
| `where(array $patterns)`     | Parametre regex kısıtlaması (`['id' => '([0-9]+)']`) |
| `get                         | post                                                 | put | patch | delete(string $path, array $handler)` | HTTP verb route'u |

### Route Parametreleri

Controller metodunda parametre olarak alınır:

```php
// Route: $router->get('/{id}', ...)->where(['id' => '([0-9]+)']);
public function getById(int $id): void { ... }

// Route: $router->post('/{id}/permission/{permissionId}', ...)->where(['id' => '([0-9]+)', 'permissionId' => '([0-9]+)']);
public function givePermission(int $id, int $permissionId): void { ... }
```

---

## 🎮 Controller Yazımı

### Base Controller (`App\Core\Abstracts\Controller`)

```php
abstract class Controller {
    protected Request $request;
    protected Response $response;

    final protected function params(?string $param = null): array|int|string {
        // Query param: ?lang=1&curr=2 -> language_id, currency_id
        return ['language_id' => ..., 'currency_id' => ...][$param] ?? ...;
    }
}
```

### Controller Template

```php
<?php
declare(strict_types=1);

namespace App\Modules\Product;

use System\Http\{Request, Response};
use App\Core\Abstracts\Controller;
use App\Modules\Product\{ProductRequest, ProductResponse, ProductService};

/**
 * @OA\Tag(name="Product", description="Ürün işlemleri")
 */
class ProductController extends Controller {
    public function __construct(
        protected Response $response,
        protected Request $request,
        protected ProductService $service
    ) {}

    // LIST
    public function getAll(): void {
        $result = $this->service->getAll($this->params('language_id'));
        $list = array_map(fn($item) => (new ProductResponse())->map($item), $result);
        $this->response->json($list);
    }

    // DETAIL
    public function getById(int $id): void {
        $result = $this->service->getOne($id, $this->params('language_id'));
        $response = (new ProductResponse())->map($result);
        $this->response->json($response);
    }

    // CREATE
    public function create(): void {
        $json = $this->request->json();
        $request = new ProductRequest();
        $request->fill($json);  // Validation burada yapılır

        $result = $this->service->createProduct($request);
        $response = (new ProductResponse())->map($result);
        $this->response->json($response, 201);
    }

    // UPDATE
    public function update(): void {
        $json = $this->request->json();
        $request = new ProductRequest();
        $request->fill($json);

        $result = $this->service->updateProduct($request);
        $response = (new ProductResponse())->map($result);
        $this->response->json($response);
    }

    // DELETE
    public function delete(int $id): void {
        $result = $this->service->deleteProduct($id);
        $this->response->json($result);
    }

    // FILE UPLOAD (multipart/form-data)
    public function uploadImage(): void {
        $files = $this->request->files('files');  // $_FILES['files']
        $result = $this->service->uploadImage($files);
        $this->response->json($result);
    }
}
```

### Request Verisi Alma

```php
$json = $this->request->json();           // application/json body -> array
$files = $this->request->files('files');  // multipart/form-data -> $_FILES['files']
$token = $this->request->bearer();        // Authorization: Bearer <token>
$langId = $this->request->get('lang');    // Query param
$ip = $this->request->userIp();           // Client IP
$ua = $this->request->userAgent();        // User-Agent
$user = $this->request->getUser();        // Auth middleware tarafından set edilen user data
```

### Response Gönderme

```php
$this->response->json($data);           // 200 OK
$this->response->json($data, 201);      // 201 Created
$this->response->json($data, 404);      // 404 Not Found
// Header'lar otomatik: Content-Type: application/json; charset=UTF-8
```

---

## 🧠 Service Yazımı

### Base Service (`App\Core\Abstracts\Service`)

Sağladığı metodlar:

- `check(Repository, fields, Resource, create)` - Duplicate kontrolü
- `validate(data, rules, labels, messages)` - Manuel validasyon
- `translate(Repository, fields, where, translations, table)` - Çoklu dil kayıtları
- `upload(files, dir)` - Dosya yükleme
- `unlink(files)` - Dosya silme

### Service Template

```php
<?php
declare(strict_types=1);

namespace App\Modules\Product;

use System\Date\Date;
use System\Exception\SystemException;
use System\Upload\Upload;
use App\Core\Abstracts\Service;
use App\Modules\Product\{ProductRepository, ProductRequest};

class ProductService extends Service {
    public function __construct(
        protected Upload $upload,
        protected Date $date,
        protected ProductRepository $repository
    ) {}

    public function getAll(): array {
        $result = $this->repository->findAll();
        return array_map(fn($product) => $this->getRelation($product), $result);
    }

    public function getOne(int $id): array {
        $result = $this->repository->findOne($id);
        if (empty($result)) {
            throw new SystemException('Record not found', 404);
        }
        return $this->getRelation($result);
    }

    public function createProduct(ProductRequest $request): array {
        return $this->repository->transaction(function () use ($request): array {
            $this->check($this->repository, ['code' => $request->code], $request);

            $create = $this->repository->create([
                'code'       => $request->code,
                'title'      => $request->title,
                'content'    => $request->content,
                'is_active'  => $request->is_active,
                'sort_order' => $request->sort_order,
                'brand_id'   => $request->brand_id,
                'stock'      => $request->stock,
                'price'      => $request->price,
                'date'       => $this->date->setDate($request->date)->getDate(Date::GENERIC3),
            ]);

            $product = $this->repository->findOne($create->lastInsertId());
            $this->createRelation($product, $request);

            return $this->getRelation($product);
        });
    }

    public function updateProduct(ProductRequest $request): array {
        return $this->repository->transaction(function () use ($request): array {
            $this->check($this->repository, ['code' => $request->code], $request, false);

            $product = $this->getOne($request->id);

            $update = $request->filter([
                'code'       => $request->code,
                'title'      => $request->title,
                'content'    => $request->content,
                'is_active'  => $request->is_active,
                'sort_order' => $request->sort_order,
                'brand_id'   => $request->brand_id,
                'stock'      => $request->stock,
                'price'      => $request->price,
                'date'       => $this->date->setDate($request->date)->getDate(Date::GENERIC3),
            ]);

            $this->repository->update($update, ['id' => $request->id]);
            $this->syncRelation($product, $request);

            return $this->getOne($request->id);
        });
    }

    public function deleteProduct(int $id): bool {
        return $this->repository->transaction(function () use ($id): bool {
            $product = $this->getOne($id);
            $images = array_values(array_column($product['image_list'] ?? [], 'image_path'));

            $this->repository->softDelete(['id' => $id, 'deleted_at' => ['IS NULL']]);
            $this->repository->hardDelete(['product_id' => $id], 'product_image');
            $this->repository->hardDelete(['product_id' => $id], 'product_category');

            if ($images) $this->unlink($images);
            return true;
        });
    }

    // Private helpers: getRelation, createRelation, syncRelation, deleteRelation, checkFields...
}
```

### Transaction Kullanımı

```php
$this->repository->transaction(function () use ($data): mixed {
    // Tüm DB işlemleri aynı transaction içinde
    $this->repository->create(...);
    $this->repository->update(...);
    return $result;
});
// Otomatik commit/rollback (Exception fırlatılırsa rollback)
```

### Validation (Manuel)

```php
$this->validate($json, [
    'email'    => ['required', 'email'],
    'password' => ['required', 'string', 'min:8'],
], [
    'email' => 'E-posta',
], [
    'required' => ':label alanı zorunludur.',
]);
// Hata durumunda SystemException(400) fırlatır
```

### Dosya Yükleme

```php
// Service::upload() kullanır (base class'tan gelir)
$paths = $this->upload($files, 'product');  // ['product/abc123.jpg', ...]

// Service::unlink() ile silme
$this->unlink($paths);  // bool
```

---

## 🗄️ Repository Yazımı

### Base Repository (`App\Core\Abstracts\Repository`)

Sağladığı metodlar (soft delete `deleted_at IS NULL` filtresi otomatik):

- `findAll()` - Tüm aktif kayıtlar
- `findAllBy(where, orderBy)` - Koşullu liste
- `findOne(id)` - ID ile tek kayıt
- `findBy(where, table)` - Koşula göre tek kayıt
- `create(fields, table)` - Insert
- `update(fields, where, table)` - Update
- `hardDelete(where, table)` - Permanent delete
- `softDelete(where, table)` - `deleted_at` set
- `transaction(callback)` - Transaction wrapper

### Repository Template

```php
<?php
declare(strict_types=1);

namespace App\Modules\Product;

use System\Database\Database;
use App\Core\Abstracts\Repository;

class ProductRepository extends Repository {
    public function __construct(
        protected Database $database,
        protected string $table = 'product'
    ) {}

    // Özel query'ler için prepare() kullanın
    public function findUserByEmail(string $email): ?array {
        return $this->database
            ->prepare('SELECT * FROM app_user WHERE email = :email')
            ->execute(['email' => $email])
            ->fetchOne();
    }

    // Relation'lar için yardımcı metodlar
    public function findAllCategory(int $productId): array {
        return $this->database
            ->prepare('SELECT c.* FROM category c
                JOIN product_category pc ON c.id = pc.category_id
                WHERE pc.product_id = :pid AND c.deleted_at IS NULL')
            ->execute(['pid' => $productId])
            ->fetchAll();
    }

    public function findAllImage(int $productId): array {
        return $this->database
            ->table('product_image')
            ->select()
            ->where(['product_id' => $productId])
            ->orderBy(['sort_order' => 'ASC'])
            ->prepare()
            ->execute()
            ->fetchAll();
    }
}
```

### Database Query Builder Kullanımı

```php
// SELECT
$db->table('product')
    ->select(['id', 'title', 'price'])
    ->where([
        'is_active' => 1,
        'category_id' => ['IN', [1,2,3]],
        'price' => ['BETWEEN', [100, 500]],
        'title' => ['LIKE', '%arama%'],
        'created_at' => ['>=', '2024-01-01'],
    ])
    ->orderBy(['sort_order' => 'ASC', 'id' => 'DESC'])
    ->limit(10)
    ->offset(0)
    ->prepare()
    ->execute()
    ->fetchAll();

// INSERT
$db->table('product')->insert([
    'title' => 'Ürün',
    'price' => 100,
])->prepare()->execute();
$lastId = $db->lastInsertId();

// UPDATE
$db->table('product')
    ->update(['price' => 150])
    ->where(['id' => 1])
    ->prepare()
    ->execute();

// DELETE (hard)
$db->table('product')->delete()->where(['id' => 1])->prepare()->execute();

// RAW query
$db->prepare('SELECT COUNT(*) as c FROM product WHERE is_active = 1')
    ->execute()
    ->fetchOne();
```

### Where Koşul Sözdizimi

```php
[
    'column' => 'value',                    // =
    'column' => ['IN', [1,2,3]],            // IN
    'column' => ['NOT IN', [1,2,3]],        // NOT IN
    'column' => ['BETWEEN', [10, 20]],      // BETWEEN
    'column' => ['LIKE', '%text%'],         // LIKE
    'column' => ['>', 100],                 // >
    'column' => ['>=', 100],                // >=
    'column' => ['<', 100],                 // <
    'column' => ['<=', 100],                // <=
    'column' => ['!=', 100],                // !=
    'column' => ['IS NULL'],                // IS NULL
    'column' => ['IS NOT NULL'],            // IS NOT NULL
    'column' => ['RAW', 'NOW()'],           // Raw SQL (dikkatli kullanın)
]
```

---

## ✅ Request/Response (Resource Pattern)

### Base Resource (`App\Core\Abstracts\Resource`)

Sağladığı metodlar:

- `fill(array $data)` - Validation + property assignment
- `map(array $data)` - Reflection + type casting + nested Resource/Collection
- `filter(array $data)` - Sadece class property'lerine karşılık gelen key'leri döndürür
- `property(?string $key)` - Non-empty property'leri döndürür
- `rules()` - Validation rules (override edilir)
- `labels()` - Field labels (override edilir)
- `messages()` - Custom error messages (override edilir)

### Request Sınıfı (Input)

```php
<?php
declare(strict_types=1);

namespace App\Modules\Product;

use App\Core\Abstracts\Resource;

class ProductRequest extends Resource {
    public ?int $id;
    public string $code;
    public string $title;
    public ?string $content;
    public int $is_active;
    public int $sort_order;
    public int $stock;
    public float $price;
    public ?string $date;

    // Relations
    public ?int $brand_id;
    public ?array $product_category;
    public ?array $image_path;

    protected function rules(): array {
        return [
            'code'               => ['required'],
            'title'              => ['required'],
            'content'            => ['nullable'],
            'is_active'          => ['required', 'numeric', 'between:0,1'],
            'sort_order'         => ['required', 'numeric'],
            'stock'              => ['required', 'numeric', 'min:0'],
            'price'              => ['required', 'numeric', 'min:0'],
            'date'               => ['nullable', 'date'],
            'brand_id'           => ['nullable', 'numeric'],
            'product_category'   => ['nullable', 'array'],
            'product_category.*' => ['numeric'],
            'image_path'         => ['nullable', 'array'],
        ];
    }

    protected function labels(): array {
        return [
            'code'               => 'Ürün kodu',
            'title'              => 'Ürün adı',
            'is_active'          => 'Aktiflik',
            'sort_order'         => 'Sıralama',
            'stock'              => 'Stok',
            'price'              => 'Fiyat',
            'product_category.*' => 'Kategori',
        ];
    }

    protected function messages(): array {
        return [
            'required' => 'Geçerli bir :label giriniz.',
            'numeric'  => ':label sayısal olmalıdır.',
            'between'  => ':label :min ile :max arasında olmalıdır.',
        ];
    }
}
```

**Kullanım:**

```php
$request = new ProductRequest();
$request->fill($json);  // Validation başarısızsa SystemException(400)

$filtered = $request->filter([
    'code' => $request->code,
    'title' => $request->title,
    // ... sadece class property'si olanlar alınır
]);
```

### Response Sınıfı (Output)

```php
<?php
declare(strict_types=1);

namespace App\Modules\Product;

use App\Core\Abstracts\{AuditTrait, Collection, Resource};
use App\Modules\Brand\BrandResponse;
use App\Modules\Category\CategoryResponse;

class ProductResponse extends Resource {
    use AuditTrait;  // created_at, updated_at, deleted_at, created_by, updated_by, deleted_by

    public int $id;
    public string $code;
    public string $title;
    public ?string $content;
    public int $is_active;
    public int $sort_order;
    public int $stock;
    public float $price;
    public ?string $date;

    // Nested resources
    public ?BrandResponse $brand;
    public Collection $category_list;
    public Collection $image_list;

    public function __construct() {
        $this->category_list = new Collection(CategoryResponse::class);
        $this->image_list = new Collection(Image::class);  // Inner class
    }
}

class Image extends Resource {
    public int $id;
    public int $product_id;
    public string $image_path;
    public int $sort_order;
}
```

**Kullanım:**

```php
$response = (new ProductResponse())->map($dbRow);
// $dbRow['category_list'] = [['id'=>1,'title'=>'Cat'], ...]
// $response->category_list -> Collection<CategoryResponse>
// $response->brand -> BrandResponse|null

$this->response->json($response);  // JsonSerializable -> JSON
```

### Collection (Nested Array -> Object)

```php
// Response constructor'da
$this->category_list = new Collection(CategoryResponse::class);

// map() içinde otomatik:
// $data['category_list'] = [['id'=>1,'title'=>'A'], ['id'=>2,'title'=>'B']]
// -> Collection<CategoryResponse> với 2 item
```

---

## 🔐 Policy (Authorization)

### Base Policy (`App\Core\Abstracts\Policy`)

Sağladığı metodlar:

- `before(user, ability)` - Super-admin bypass (default: true döner)
- `after(user, ability, result)` - Sonuç override
- `viewAny|view|create|update|delete(user, model?)` - CRUD yetkileri
- `hasPermission(user, permission)` - Tek permission kontrolü
- `hasAnyPermission(user, permissions[])` - Herhangi biri
- `hasAllPermission(user, permissions[])` - Hepsi
- `hasRole(user, role)` - Role kontrolü
- `hasAnyRole|hasAllRole` - Role dizisi

### Policy Template

```php
<?php
declare(strict_types=1);

namespace App\Modules\Product;

use App\Core\Abstracts\Policy;

class ProductPolicy extends Policy {
    public function viewAny(?array $user): bool {
        return $this->hasPermission($user, 'product:viewAny');
    }

    public function view(?array $user, mixed $model = null): bool {
        return $this->hasPermission($user, 'product:view');
    }

    public function create(?array $user): bool {
        return $this->hasPermission($user, 'product:create');
    }

    public function update(?array $user, mixed $model = null): bool {
        return $this->hasPermission($user, 'product:update');
    }

    public function delete(?array $user, mixed $model = null): bool {
        return $this->hasPermission($user, 'product:delete');
    }
}
```

**Controller'da kullanım (manuel):**

```php
$policy = new ProductPolicy();
$user = $this->request->getUser();  // Auth middleware set eder

if (!$policy->create($user)) {
    throw new SystemException('Yetkiniz yok', 403);
}
```

---

## 🗃️ Migration Yazımı

### Base Migration (`App\Core\Abstracts\Migration`)

```php
abstract class Migration {
    protected Database $database;
    abstract public function up(): void;
    abstract public function down(): void;
    protected function defaults(): string {  // Audit + soft delete
        return " `deleted_at` TIMESTAMP NULL DEFAULT NULL,
                 `deleted_by` INT NULL DEFAULT NULL,
                 `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                 `created_by` INT NOT NULL DEFAULT 0,
                 `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                 `updated_by` INT NOT NULL DEFAULT 0 ";
    }
}
```

### Migration Template

```php
<?php
declare(strict_types=1);

use App\Core\Abstracts\Migration;

class product extends Migration {
    public function up(): void {
        $this->database->query("CREATE TABLE IF NOT EXISTS `product` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `code` VARCHAR(50) NOT NULL UNIQUE,
            `title` VARCHAR(255) NOT NULL,
            `content` TEXT NULL,
            `is_active` BOOLEAN NOT NULL DEFAULT 1,
            `sort_order` INT NOT NULL DEFAULT 0,
            `brand_id` INT NULL,
            `stock` INT NOT NULL DEFAULT 0,
            `price` DECIMAL(10,2) NOT NULL DEFAULT 0,
            `date` DATE NULL,
            {$this->defaults()}
        )");

        // Index'ler
        $this->database->query("CREATE INDEX idx_product_brand ON `product` (brand_id)");
        $this->database->query("CREATE INDEX idx_product_active ON `product` (is_active)");
    }

    public function down(): void {
        $this->database->query("DROP TABLE IF EXISTS `product`");
    }
}
```

**Naming:** Dosya adı `App/Migrations/timestamp_table.php`, sınıf adı `table` (lowercase).

**Çalıştırma:**

```bash
php cli migrate
```

---

## 🌱 Seeder Yazımı

### Base Seeder (`App\Core\Abstracts\Seeder`)

```php
abstract class Seeder {
    protected Database $database;
    abstract public function run(): void;
}
```

### Seeder Template

```php
<?php
declare(strict_types=1);

namespace App\Seeds;

use App\Core\Abstracts\Seeder;
use Exception;

class ExampleSeeder extends Seeder {
    public function run(): void {
        try {
            $this->database->table('example')->insert([
                'title' => ['Örnek 1'],
                'content' => ['İçerik 1'],
                'is_active' => [1],
            ])->prepare()->execute();

            echo "· Example seeded\n";
        } catch (Exception $e) {
            echo "✗ Example seed failed: " . $e->getMessage() . "\n";
        }
    }
}
```

**Çalıştırma:**

```bash
php cli seed
```

---

## 🎯 Validation Rules Reference

`System\Validation\Validation` sınıfı tarafından desteklenen kurallar:

| Rule                  | Açıklama                 | Parametre       |
| --------------------- | ------------------------ | --------------- |
| `required`            | Alan zorunlu             | -               |
| `string`              | String olmalı            | -               |
| `numeric`             | Sayısal olmalı           | -               |
| `integer`             | Integer olmalı           | -               |
| `float`               | Float olmalı             | -               |
| `boolean`             | Boolean olmalı           | -               |
| `email`               | Geçerli email formatı    | -               |
| `date`                | Geçerli tarih (Y-m-d)    | -               |
| `datetime`            | Geçerli datetime         | -               |
| `min:X`               | Minimum değer/uzunluk    | X               |
| `max:X`               | Maksimum değer/uzunluk   | X               |
| `between:X,Y`         | Aralık (dahil)           | X, Y            |
| `in:X,Y,Z`            | Belirli değerlerden biri | comma-separated |
| `not_in:X,Y`          | Belirli değerler hariç   | comma-separated |
| `regex:PATTERN`       | Regex eşleşme            | PCRE pattern    |
| `array`               | Array olmalı             | -               |
| `nullable`            | Null olabilir            | -               |
| `unique:table,column` | Veritabanında benzersiz  | table,column    |
| `exists:table,column` | Veritabanında var olmalı | table,column    |

**Örnek:**

```php
'email' => ['required', 'email', 'unique:app_user,email'],
'status' => ['required', 'in:active,inactive,pending'],
'age' => ['required', 'integer', 'between:18,100'],
'tags' => ['nullable', 'array'],
'tags.*' => ['string', 'max:50'],
```

---

## 📝 Kod Standartları ve İpuçları

### 1. Strict Types

```php
declare(strict_types=1);  // Her dosyanın en başında
```

### 2. Constructor Property Promotion

```php
public function __construct(
    protected ProductRepository $repository,
    protected Upload $upload
) {}
```

### 3. Return Type Declarations

```php
public function getAll(): array
public function getOne(int $id): array
public function create(Request $request): array
public function delete(int $id): bool
```

### 4. Exception Handling

```php
throw new SystemException('Record not found', 404);
throw new SystemException('Validation failed', 400);
throw new SystemException('Unauthorized', 401);
```

### 5. Config Erişimi

```php
$jwtExpire = import_config('defines.jwt.expire');  // ['access' => 3600, 'refresh' => 5184000]
$uploadConfig = import_config('defines.upload.local');
$rateLimit = import_config('defines.rate_limit');
```

### 6. Env Değişkeni

```php
$dbHost = get_env('DB_HOST');
$cfAccountId = get_env('CF_ACCOUNT_ID');
```

---

## 🔄 Yaygın Geliştirme Senaryoları

### Çoklu Dil (Translation) Desteği

`Service::translate()` kullanın:

```php
// CategoryService::createCategory() örneği
$this->translate($this->repository,
    ['title', 'content', 'url', 'meta_title', 'meta_description', 'meta_keywords'],
    ['category_id' => $categoryId],
    $request->translate ?? [],  // [{language_id: 1, title: '...', ...}, ...]
    'category_translate'
);
```

### Soft Delete vs Hard Delete

```php
// Soft delete (varsayılan)
$repo->softDelete(['id' => $id, 'deleted_at' => ['IS NULL']]);

// Hard delete (gerçekten sil)
$repo->hardDelete(['id' => $id]);
```

### Relation Sync (Many-to-Many)

```php
private function syncRelation(array $product, ProductRequest $request): void {
    $this->deleteRelation($product, $request);  // Eski relation'ları sil
    $this->createRelation($product, $request);  // Yenilerini ekle
}
```

### File Upload + Cleanup on Error

```php
try {
    $create = $this->repository->create([...]);
    // ...
} catch (SystemException $e) {
    if (isset($request->image_path)) {
        $this->unlink($request->image_path);  // Yüklenen dosyayı temizle
    }
    throw $e;
}
```

---

> **Özet:** Framework'ün pattern'i **Controller → Service → Repository** akışına dayanır. Validation `Request::fill()` ile, transaction `Repository::transaction()` ile, response `Response::json()` ile yapılır. Yeni modül yazarken mevcut modüllere (`Product`, `Category`, `User`) referans alın.
