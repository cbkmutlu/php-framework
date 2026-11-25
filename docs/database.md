# Veritabanı Erişimi ve Yönetimi

Bu belge framework'ün veritabanı katmanını, migration/seeder sistemini ve repository pattern kullanımını açıklar.

## 🗄️ Database Bileşeni (`System\Database\Database`)

Framework'ün veritabanı erişim katmanı `System\Database\Database` sınıfıdır. PDO wrapper + query builder sağlar.

### Provider Kaydı

```php
// Defines.php
'database' => [System\Database\Database::class, true],  // Singleton
```

### Temel Kullanım

```php
// Container'dan alma
$db = $container->get('database');

// Repository içinde (DI ile gelir)
class ProductRepository extends Repository {
    public function __construct(protected Database $database) {}
}
```

## 🔨 Query Builder API

### SELECT

```php
// Basit select
$db->table('product')
    ->select(['id', 'title', 'price'])
    ->where(['is_active' => 1])
    ->prepare()
    ->execute()
    ->fetchAll();

// Join ile
$db->prepare('
    SELECT p.*, b.title as brand_title
    FROM product p
    LEFT JOIN brand b ON p.brand_id = b.id
    WHERE p.deleted_at IS NULL
')->execute()->fetchAll();

// Sıralama, limit, offset
$db->table('product')
    ->select()
    ->where(['is_active' => 1])
    ->orderBy(['sort_order' => 'ASC', 'id' => 'DESC'])
    ->limit(20)
    ->offset(0)
    ->prepare()
    ->execute()
    ->fetchAll();
```

### WHERE Koşulları

```php
->where([
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
    'column' => ['RAW', 'NOW()'],           // Raw SQL expression
])
```

### INSERT

```php
// Tek kayıt
$result = $db->table('product')->insert([
    'title' => 'Ürün',
    'price' => 100.00,
    'is_active' => 1,
])->prepare()->execute();

$lastId = $db->lastInsertId();

// Çoklu kayıt (bulk)
$db->table('product')->insert([
    'title' => ['Ürün 1', 'Ürün 2'],
    'price' => [100, 200],
    'is_active' => [1, 1],
])->prepare()->execute();
```

### UPDATE

```php
$db->table('product')
    ->update(['price' => 150, 'updated_at' => ['NOW(3)']])
    ->where(['id' => 1])
    ->prepare()
    ->execute();

// Affected rows
$affected = $db->affectedRows();
```

### DELETE

```php
// Hard delete
$db->table('product')->delete()->where(['id' => 1])->prepare()->execute();

// Soft delete (manuel)
$db->table('product')
    ->update(['deleted_at' => ['NOW(3)'], 'deleted_by' => 1])
    ->where(['id' => 1, 'deleted_at' => ['IS NULL']])
    ->prepare()
    ->execute();
```

### RAW Query

```php
// Prepared statement
$result = $db->prepare('SELECT * FROM product WHERE price > :min AND is_active = 1')
    ->execute(['min' => 100])
    ->fetchAll();

// Tek satır
$row = $db->prepare('SELECT COUNT(*) as total FROM product WHERE is_active = 1')
    ->execute()
    ->fetchOne();

// Raw execute (prepared statement olmadan - DİKKATLI KULLANIN)
$db->query('SET NAMES utf8mb4')->execute();
```

### Transaction

```php
try {
    $db->transaction();  // BEGIN

    $db->table('product')->insert([...])->prepare()->execute();
    $db->table('product_image')->insert([...])->prepare()->execute();

    $db->commit();  // COMMIT
} catch (Exception $e) {
    $db->rollback();  // ROLLBACK
    throw $e;
}
```

**Repository'de wrapper:**

```php
// App\Core\Abstracts\Repository::transaction()
$this->repository->transaction(function () use ($data) {
    $this->repository->create([...]);
    $this->repository->update([...]);
    return $result;
});
```

## 📦 Repository Pattern (`App\Core\Abstracts\Repository`)

Tüm modül repository'leri bu base class'tan extend edilir.

### Base Metodlar

```php
abstract class Repository {
    protected Database $database;
    protected string $table;

    // Tüm aktif kayıtlar (deleted_at IS NULL)
    public function findAll(): array

    // Koşullu liste
    public function findAllBy(?array $where = [], ?array $orderBy = null): array

    // ID ile tek kayıt
    public function findOne(int $id): ?array

    // Koşula göre tek kayıt
    public function findBy(array $where, ?string $table = null): ?array

    // Insert
    public function create(array $fields, ?string $table = null): Database

    // Update
    public function update(array $fields, array $where, ?string $table = null): Database

    // Hard delete
    public function hardDelete(array $where, ?string $table = null): Database

    // Soft delete
    public function softDelete(array $where, ?string $table = null): Database

    // Transaction wrapper
    final public function transaction(callable $callback): mixed
}
```

### Özel Repository Metodları

Her modül kendi özel query'lerini repository'ye ekler:

```php
class ProductRepository extends Repository {
    public function __construct(
        protected Database $database,
        protected string $table = 'product'
    ) {}

    // Çoklu dil (translation) için
    public function findTranslation(int $productId, int $languageId): ?array {
        return $this->database
            ->prepare('SELECT * FROM product_translate WHERE product_id = :pid AND language_id = :lid')
            ->execute(['pid' => $productId, 'lid' => $languageId])
            ->fetchOne();
    }

    // Relation'lar
    public function findAllCategory(int $productId): array { ... }
    public function findAllImage(int $productId): array { ... }
    public function findBrand(int $brandId): ?array { ... }

    // Image path by id
    public function findImagePath(int $imageId): ?string { ... }
}
```

## 🌱 Migration Sistemi

### Base Migration (`App\Core\Abstracts\Migration`)

```php
abstract class Migration {
    protected Database $database;
    abstract public function up(): void;
    abstract public function down(): void;

    // Audit fields + soft delete
    protected function defaults(): string {
        return " `deleted_at` TIMESTAMP NULL DEFAULT NULL,
                 `deleted_by` INT NULL DEFAULT NULL,
                 `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                 `created_by` INT NOT NULL DEFAULT 0,
                 `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                 `updated_by` INT NOT NULL DEFAULT 0 ";
    }
}
```

### Migration Dosyası

`App/Migrations/0000_00_00_000_product.php`:

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

        $this->database->query("CREATE INDEX idx_product_brand ON `product` (brand_id)");
        $this->database->query("CREATE INDEX idx_product_active ON `product` (is_active)");
    }

    public function down(): void {
        $this->database->query("DROP TABLE IF EXISTS `product`");
    }
}
```

### Naming Convention

| Unsur     | Format                                   | Örnek                                       |
| --------- | ---------------------------------------- | ------------------------------------------- |
| Dosya adı | `App/Migrations/{timestamp}_{table}.php` | `0000_00_00_000_product.php`                |
| Sınıf adı | Tablo adı (lowercase)                    | `product`                                   |
| Tablo adı | `snake_case`, tekil                      | `product`, `product_image`, `app_user_role` |

### Migration Çalıştırma

```bash
# Tüm migration'ları çalıştır (henüz çalışmamışları)
php cli migrate

# Belirli bir migration'ı çalıştır (manuel)
php -r "require 'App/Config/Constants.php'; require 'vendor/autoload.php'; (new \App\Migrations\product(\$container->get('database')))->up();"
```

### CLI Komutu (`cli` dosyası)

```bash
#!/usr/bin/php
<?php
// ...
$cli = new \System\Cli\Cli();
echo $cli->run($params);
```

`System\Cli\Cli` sınıfı `migrate` ve `seed` komutlarını işler.

## 🌿 Seeder Sistemi

### Base Seeder (`App\Core\Abstracts\Seeder`)

```php
abstract class Seeder {
    protected Database $database;
    abstract public function run(): void;
}
```

### Seeder Dosyası

`App/Seeds/01_AuthorizationSeeder.php`:

```php
<?php
declare(strict_types=1);

namespace App\Seeds;

use App\Core\Abstracts\Seeder;
use Exception;

class AuthorizationSeeder extends Seeder {
    public function run(): void {
        $config = import_config('authorization');

        try {
            // Permissions
            $permissions = $config['permissions'] ?? [];
            // ... bulk insert with duplicate check

            // Roles
            $roles = $config['roles'] ?? [];
            // ... bulk insert + permission sync

            // User roles
            $users = $config['users'] ?? [];
            // ... assign roles

            echo "· Authorization seeded\n";
        } catch (Exception $e) {
            echo "✗ Authorization seed failed: " . $e->getMessage() . "\n";
        }
    }

    private function syncRole(int $roleId, string|array $permissions): void { ... }
    private function assignRole(int $userId, int $roleId, string $scopeType): void { ... }
}
```

### Seeder Çalıştırma

```bash
php cli seed
```

**Sıralama:** Dosya adındaki prefix (01*, 02*) sıralamayı belirler.

## 🔗 Tablo İlişkileri ve Yaygın Pattern'ler

### 1. One-to-Many (Product -> ProductImage)

```php
// ProductRepository
public function findAllImage(int $productId): array {
    return $this->database
        ->table('product_image')
        ->select()
        ->where(['product_id' => $productId])
        ->orderBy(['sort_order' => 'ASC'])
        ->prepare()->execute()->fetchAll();
}

// Service'de relation yükleme
private function getRelation(array $product): array {
    $product['image_list'] = $this->repository->findAllImage($product['id']);
    return $product;
}
```

### 2. Many-to-Many (Product <-> Category)

**Ara tablo:** `product_category` (product_id, category_id)

```php
// Service::createRelation()
$fields = array_map(fn($catId) => [
    'product_id' => $product['id'],
    'category_id' => $catId,
], $request->product_category);

$this->repository->create($fields, 'product_category');

// Service::syncRelation() - Update'te eski sil, yeni ekle
$this->repository->hardDelete(['product_id' => $id], 'product_category');
$this->createRelation($product, $request);
```

### 3. Translation (Çoklu Dil)

**Ana tablo:** `category` (id, code, is_active, sort_order, image_path, ...)
**Çeviri tablosu:** `category_translate` (id, category_id, language_id, title, content, url, meta_title, meta_description, meta_keywords)

```php
// Service::translate() base metodu kullanılır
$this->translate($this->repository,
    ['title', 'content', 'url', 'meta_title', 'meta_description', 'meta_keywords'],
    ['category_id' => $categoryId],
    $request->translate ?? [],  // [{language_id: 1, title: '...'}, ...]
    'category_translate'
);
```

### 4. Audit Fields (Otomatik)

Migration'da `$this->defaults()` ile eklenir:

```sql
`deleted_at` TIMESTAMP NULL DEFAULT NULL,
`deleted_by` INT NULL DEFAULT NULL,
`created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
`created_by` INT NOT NULL DEFAULT 0,
`updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
`updated_by` INT NOT NULL DEFAULT 0
```

**Resource'da `AuditTrait`:**

```php
use App\Core\Abstracts\AuditTrait;

class CategoryResponse extends Resource {
    use AuditTrait;
    // public ?string $created_at;
    // public ?int $created_by;
    // public ?string $updated_at;
    // public ?int $updated_by;
    // public ?string $deleted_at;
    // public ?int $deleted_by;
}
```

### 5. Soft Delete Pattern

**Repository base metodları zaten `deleted_at IS NULL` filtresi uygular:**

```php
// findAll() -> WHERE deleted_at IS NULL
// findOne() -> WHERE id = ? AND deleted_at IS NULL
// findAllBy() -> WHERE deleted_at IS NULL AND ...

// Soft delete
$repo->softDelete(['id' => $id, 'deleted_at' => ['IS NULL']]);

// Hard delete (gerçekten sil)
$repo->hardDelete(['id' => $id]);

// Sadece soft-deleted kayıtları bulmak için
$repo->findBy(['deleted_at' => ['IS NOT NULL']]);  // Base metod override gerekebilir
```

## 📊 Veritabanı Şeması Özeti (Mevcut Tablolar)

| Tablo                 | Açıklama            | Ana Alanlar                                                                                      |
| --------------------- | ------------------- | ------------------------------------------------------------------------------------------------ |
| `app_user`            | Kullanıcılar        | id, name, surname, email, password, status                                                       |
| `app_user_token`      | Refresh token'lar   | id, jti, user_id, hash_token, user_ip, user_agent, expires_at, revoked_at, requested_at, next_id |
| `app_role`            | Roller              | id, name, slug, description                                                                      |
| `app_permission`      | İzinler             | id, name, slug, group_name                                                                       |
| `app_role_permission` | Role-İzin           | role_id, permission_id                                                                           |
| `app_user_role`       | Kullanıcı-Rol       | user_id, role_id, scope_type, scope_id                                                           |
| `app_user_permission` | Kullanıcı-İzin      | user_id, permission_id, type, scope_type, scope_id                                               |
| `product`             | Ürünler             | id, code, title, content, brand_id, stock, price, date, is_active, sort_order                    |
| `product_image`       | Ürün resimleri      | id, product_id, image_path, sort_order                                                           |
| `product_category`    | Ürün-Kategori       | product_id, category_id                                                                          |
| `category`            | Kategoriler         | id, code, is_active, sort_order, image_path                                                      |
| `category_translate`  | Kategori çevirileri | category*id, language_id, title, content, url, meta*\*                                           |
| `brand`               | Markalar            | id, code, title, is_active, sort_order, image_path                                               |
| `approval_flow`       | Onay akışları       | id, entity_type, action, payload, current_step, total_steps, status, created_by                  |
| `approval_step`       | Onay adımları       | id, flow_id, step_order, assignee_type, assignee_id, status, decided_by, decided_at, comment     |
| `approval_template`   | Onay şablonları     | entity_type, action, steps (JSON)                                                                |

## ⚡ Performans İpuçları

1. **Index kullanın** - FK alanlarda, `is_active`, `deleted_at`, sıralama alanlarında
2. **Bulk insert** - Çoklu kayıtta `insert([['a','b'], ['c','d']])` kullanın
3. **Select sütunları belirtin** - `select(['id','title'])` yerine `select()` (hepsi)
4. **N+1 sorgusunu önleyin** - Relation'ları tek sorguda veya batch'lerde çekin
5. **Prepared statements** - Her zaman `prepare()->execute(params)` kullanın
6. **APCu cache** - Sık okunan verilerde `Cache` provider kullanın

---

> **Not:** Framework ORM (Eloquent, Doctrine) **kullanmaz**. `System\Database\Database` query builder + `Repository` pattern ile veri erişimi sağlanır. Bu basitlik, performans ve SQL üzerinde tam kontrol avantajı sağlar.
