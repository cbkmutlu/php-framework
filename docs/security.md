# Güvenlik

Bu belge framework'ün sağladığı güvenlik mekanizmalarını ve geliştiricinin sorumluluk alanlarını açıklar.

## 🔐 Authentication (Kimlik Doğrulama)

### JWT Tabanlı Stateless Auth

Framework **JWT (JSON Web Token)** kullanır, session/cookie tabanlı **değildir**.

**Token Yapısı:**

- **Access Token**: Kısa ömürlü (default 1 saat / 3600 sn), API isteklerinde `Authorization: Bearer <token>`
- **Refresh Token**: Uzun ömürlü (default 60 gün / 5184000 sn), access token yenilemek için

### Token Üretimi (`AuthService`)

```php
// Access token (JWT)
$accessToken = $this->jwt->createToken([
    'id' => $userId,
    'jti' => $jti,           // JWT ID (token rotation için)
    'exp' => time() + 3600,  // Expire timestamp
]);

// Refresh token (random bytes -> hex)
$refreshToken = bin2hex(random_bytes(32));
// Hash'lenerek DB'de saklanır: hash('sha256', $refreshToken)
```

### Token Doğrulama (`Auth` Middleware)

```php
// App\Core\Middlewares\Auth
public function handle(callable $next): mixed {
    $token = $this->request->bearer();  // Authorization header'dan alır
    $decode = $this->jwt->parseToken($token);  // Verify + decode
    $this->request->setUser($decode);  // Request'e user data ekler
    return $next();
}
```

**Controller'da user erişimi:**

```php
$user = $this->request->getUser();  // ['id' => 1, 'jti' => '...', 'exp' => ...]
$userId = $user['id'] ?? null;
```

### Login Akışı (`AuthController::login`)

```php
public function login(): void {
    $json = $this->request->json();
    $this->service->validate($json, [
        'email'    => ['required', 'email'],
        'password' => ['required', 'string'],
    ]);

    $result = $this->service->login($json['email'], $json['password']);
    // $result = ['user_id', 'user_email', 'access_token', 'refresh_token']
    $this->response->json($result);
}
```

**Service'de (`AuthService::login`):**

1. Email ile user bul
2. `Crypt::verify($password, $hash)` - Argon2ID doğrulama
3. JTI üret (unique)
4. Access token (JWT) üret
5. Refresh token üret -> hash'le -> `app_user_token` tablosuna kaydet
6. Her iki token'ı döndür

### Token Rotation (Refresh)

`AuthService::refresh()` - **Güvenli token rotation:**

1. Refresh token hash'le -> DB'de bul
2. User-Agent kontrolü (token çalınma koruması)
3. **Lock mekanizması** (APCu + DB `requested_at`):
   - Aynı refresh token'ın eşzamanlı kullanımını engeller
   - `apcu_add('refresh_lock:' . $hash, 1, 3)` - 3 saniye kilit
4. Token rotation:
   - Yeni access token + yeni refresh token üret
   - Eski refresh token'ı `revoked_at` + `next_id` ile zincirle
5. Lock release

### Logout

```php
// Single device logout
$service->logout($refreshToken);  // Hash -> DB bul -> revoked_at set

// All devices logout
$service->logoutAll($userId);  // User'ın tüm aktif token'ları revoke
```

---

## 🛡️ Authorization (Yetkilendirme)

### Role / Permission Sistemi

**Hiyerarşi:**

```
User -> Role(s) -> Permission(s)
User -> Direct Permission(s) (allow/deny)
Scope: system | tenant | resource (gelecekte)
```

### Permission Formatı

`resource:action` örn. `product:create`, `user:viewAny`, `role:delete`

**Wildcard:** `*` (super-admin, admin için)

### Gate Sistemi (`System\Gate\Gate`)

Container provider: `gate` (singleton)

**Provider:** `App\Core\Handlers\Gate\ModuleHandler` (Defines.php `gate.providers.module`)

```php
class ModuleHandler implements GateInterface {
    public function __construct(protected UserService $userService) {}

    public function getPermission(int $userId): array {
        return $this->userService->getPermission($userId);
        // Returns: ['id' => $userId, 'roles' => [...], 'permissions' => [...]]
    }
}
```

**UserService::getPermission()** - Cache'li (Defines.php `gate.cache_expire`: 3600sn):

- User'ın rollerini getir
- Role permission'larını getir
- User direct permission'larını getir
- Merge et -> cache'le

### Policy Pattern (`App\Core\Abstracts\Policy`)

Her modül `Policy` sınıfı tanımlar:

```php
class ProductPolicy extends Policy {
    public function viewAny(?array $user): bool {
        return $this->hasPermission($user, 'product:viewAny');
    }
    public function create(?array $user): bool {
        return $this->hasPermission($user, 'product:create');
    }
    // view, update, delete ...
}
```

**Base Policy metodları:**

- `before(user, ability)` - Super-admin bypass (default: `super-admin` rol varsa `true`)
- `after(user, ability, result)` - Sonuç override
- `hasPermission(user, perm)` - Tek permission
- `hasAnyPermission(user, perms[])` - Herhangi biri
- `hasAllPermission(user, perms[])` - Hepsi
- `hasRole(user, role)` - Role kontrolü
- `hasAnyRole/hasAllRole` - Role dizisi

### Controller'da Yetki Kontrolü

**Manuel (önerilen - explicit):**

```php
$policy = new ProductPolicy();
$user = $this->request->getUser();

if (!$policy->create($user)) {
    throw new SystemException('Yetkiniz yok', 403);
}
```

**Middleware tabanlı (yok - framework'de built-in policy middleware yok):**
Geliştirici controller'da explicit kontrol yapmalıdır.

---

## ✅ Input Validation

### Request Sınıfında (`Resource::fill()`)

```php
class ProductRequest extends Resource {
    protected function rules(): array {
        return [
            'title' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
            'email' => ['required', 'email'],
            'tags'  => ['nullable', 'array'],
            'tags.*' => ['string', 'max:50'],
        ];
    }
}
```

**Controller'da:**

```php
$request = new ProductRequest();
$request->fill($json);  // Validation FAIL -> SystemException(400) with JSON errors
```

### Service'de Manuel Validation

```php
$this->validate($data, [
    'field' => ['required', 'email'],
], [
    'field' => 'E-posta',
], [
    'required' => ':label zorunludur.',
]);
```

### Validation Rules (System\Validation\Validation)

| Rule                                           | Açıklama                   |
| ---------------------------------------------- | -------------------------- |
| `required`                                     | Zorunlu                    |
| `string`/`numeric`/`integer`/`float`/`boolean` | Tip kontrolü               |
| `email`                                        | Email formatı              |
| `date`/`datetime`                              | Tarih formatı              |
| `min:X` / `max:X`                              | Min/max değer veya uzunluk |
| `between:X,Y`                                  | Aralık                     |
| `in:a,b,c`                                     | Belirli değerlerden biri   |
| `regex:PATTERN`                                | Regex eşleşme              |
| `array`                                        | Array olmalı               |
| `nullable`                                     | Null olabilir              |
| `unique:table,col`                             | DB unique check            |
| `exists:table,col`                             | DB exists check            |

---

## 🗄️ SQL Güvenliği

### Prepared Statements (Zorunlu)

**Framework `Database` sınıfı prepared statement kullanır:**

```php
// GÜVENLİ
$db->prepare('SELECT * FROM product WHERE id = :id AND is_active = 1')
   ->execute(['id' => $userInput])
   ->fetchOne();

$db->table('product')
   ->where(['id' => $userInput])  // Otomatik parameter binding
   ->prepare()
   ->execute()
   ->fetchOne();
```

**KESİNLIKLE YAPILMAYACAKLAR:**

```php
// TEHLİKELİ - SQL Injection riski
$db->query("SELECT * FROM product WHERE id = $userInput")->execute();
$db->prepare("SELECT * FROM product WHERE id = $userInput")->execute();  // Parameter binding yok
```

### Repository Base Metodları Güvenlidir

```php
$repo->findOne($id);           // :id parameter binding
$repo->findBy(['email' => $email]);  // :email parameter binding
$repo->create(['title' => $title]);  // Parameter binding
$repo->update($fields, $where);      // Parameter binding
```

### Raw Query Gerekiyorsa

```php
// Parameter binding ile
$db->prepare('SELECT * FROM product WHERE price > :min AND title LIKE :search')
   ->execute(['min' => 100, 'search' => '%' . $term . '%'])
   ->fetchAll();
```

---

## 🔒 Şifreleme ve Hashing

### `System\Crypt\Crypt` (Provider: `crypt`, singleton)

**Config (Defines.php):**

```php
'crypt' => [
    'secret'    => '...',  // AES encryption key (32 byte hex)
    'cipher'    => 'aes-128-cbc',
    'phrase'    => 'sha256',
    'cost'      => 10,
    'algorithm' => PASSWORD_ARGON2ID,
],
```

**Metodlar:**

```php
// Password hash (Argon2ID)
$hash = $crypt->hash($password);  // $argon2id$v=19$m=65536,t=4,p=1$...

// Password verify
$valid = $crypt->verify($password, $hash);

// AES Encryption
$encrypted = $crypt->encrypt($data);  // Base64 encoded
$decrypted = $crypt->decrypt($encrypted);
```

**Kullanım Alanları:**

- User password (Argon2ID)
- Session/cookie encryption (AES)
- Hassas veri şifreleme (AES)

---

## 🍪 Session ve Cookie Güvenliği

### Session (`System\Session\Session`)

**Config (Defines.php):**

```php
'session' => [
    'encryption_key'   => '...',  // 64 char hex
    'cookie_httponly'  => true,
    'use_only_cookies' => true,
    'lifetime'         => 3600,
    'session_name'     => '_SESSID',
    'samesite'         => 'Lax',
],
```

**Özellikler:**

- Encrypted cookie storage
- HttpOnly, SameSite=Lax
- `use_only_cookies=1` (URL'de session ID yok)

### Cookie (`System\Cookie\Cookie`)

**Config (Defines.php):**

```php
'cookie' => [
    'encryption_key'  => '...',  // 64 char hex
    'cookie_security' => true,   // Secure flag (HTTPS)
    'separator'       => '--',
    'httponly'        => true,
    'secure'          => false,  // Production'da true olmalı
    'path'            => '/',
    'domain'          => '',
    'samesite'        => 'Lax',
];
```

---

## 🌐 CORS ve Security Headers

### CORS Config (Defines.php `header`)

```php
'header' => [
    'allow-origin'      => '*',  // Production'da specific domain!
    'allow-headers'     => 'Cache-Control, Pragma, Origin, Content-Type, Authorization, X-Requested-With',
    'allow-methods'     => 'GET, POST, PUT, DELETE, PATCH, OPTIONS',
    'allow-credentials' => false,
],
```

**Middleware (`Security`) - Otomatik header set etmez, sadece validation yapar:**

```php
// App\Core\Middlewares\Security
public function handle(callable $next): mixed {
    if ($this->request->isRobot()) {
        throw new SystemException('request_is_robot', 403);
    }
    if (!$this->request->isUri()) {
        throw new SystemException('request_is_not_valid_uri', 400);
    }
    return $next();
}
```

**CORS header'ları manuel eklenmeli veya Apache/Nginx config'inde ayarlanmalı.**

### `.htaccess` (Public/.htaccess)

```apache
# Authorization header pass-through (CGI/FastCGI için)
RewriteCond %{HTTP:Authorization} .
RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

# Trailing slash redirect
RewriteCond %{REQUEST_FILENAME} !-d
RewriteCond %{REQUEST_URI} ^(.+)/$
RewriteRule ^ %1 [L,R=301]

# Front controller
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^ index.php [QSA,L]
```

### `subdir.htaccess` (Alt dizin deployment için)

```apache
# App, System, vendor erişimini engelle
<DirectoryMatch "^/(App|System|vendor)">
    Require all denied
</DirectoryMatch>

# Dotfile erişimini engelle
RewriteCond %{REQUEST_URI} (^|/)\.
RewriteRule .* - [F,L]

# Upload dispatcher
RewriteRule ^upload/(.*)$ Public/upload/$1 [L]

# Route dispatcher
RewriteRule ^(.*)$ Public/$1 [L]
```

---

## 🚫 Rate Limiting

### `RateLimit` Middleware

**Config (Defines.php):**

```php
'rate_limit' => [
    'max_attempts'  => 10,      // Dakika başına max istek
    'decay_minutes' => 5,       // Sıfırlama süresi
    'enabled'       => true,
    'whitelist'     => ['127.0.0.1', '::1'],
    'custom_limits' => [
        '/v1/auth/login' => ['max_attempts' => 10, 'decay_minutes' => 5],
        '/v1/auth/register' => ['max_attempts' => 5, 'decay_minutes' => 5],
    ],
],
```

**Çalışma Prensibi:**

- Key: `rate_limit:sha1(ip|userAgent|endpoint)`
- Storage: APCu (varsa) veya File Cache
- Headers: `X-RateLimit-Limit`, `X-RateLimit-Remaining`, `X-RateLimit-Reset`, `Retry-After`

**Route'a ekleme:**

```php
$router->prefix('api/v1/auth')->middleware([RateLimit::class])->group(...);
```

---

## 📁 Dosya Yükleme Güvenliği

### `System\Upload\Upload` Provider

**Config (Defines.php):**

```php
'upload' => [
    'default'       => 'local',
    'allowed_types' => ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'txt'],
    'allowed_mimes' => ['image/jpeg', 'image/png', 'image/gif', 'application/pdf', 'text/*'],
    'providers' => [
        'local' => [
            'handler' => App\Core\Handlers\Upload\LocalHandler::class,
            'path' => 'Public/upload',
        ],
        'cloudflare' => [
            'handler' => App\Core\Handlers\Upload\CloudflareHandler::class,
            // R2/S3 credentials env'den
        ],
    ],
],
```

**Güvenlik Kontrolleri (Handler'da):**

- MIME type kontrolü (`finfo` ile)
- Extension kontrolü
- Dosya boyutu sınırı (php.ini `upload_max_filesize`, `post_max_size`)
- Güvenli dosya adı üretimi (random + extension)

**Service Kullanımı:**

```php
$paths = $this->upload($files, 'product');  // ['product/abc123.jpg', ...]
$this->unlink($paths);  // Silme
```

---

## 🛡️ CSRF Koruması

### `Csrf` Middleware (`App\Core\Middlewares\Csrf`)

**Not:** Mevcut implementasyonda `Csrf` middleware route'lara eklenmemiş. API (stateless JWT) için genellikle gerekli değildir. Form tabanlı web sayfaları için eklenebilir.

---

## 🔑 Geliştirici Sorumlulukları Checklist

| Alan              | Framework Sağlar                               | Geliştirici Yapmalı                                                  |
| ----------------- | ---------------------------------------------- | -------------------------------------------------------------------- |
| **SQL Injection** | Prepared statements (Database class)           | Raw query kullanmama, `prepare()->execute(params)` kullanma          |
| **XSS**           | JSON response (Content-Type: application/json) | HTML output etmemek, API context'te güvenli                          |
| **CSRF**          | Csrf middleware (mevcut ama kullanılmamış)     | Stateful form varsa middleware ekle                                  |
| **Auth**          | JWT middleware, token rotation                 | Controller'da `getUser()` ile kimlik kontrolü                        |
| **AuthZ**         | Gate, Policy base class                        | Her controller action'da policy check                                |
| **Validation**    | Resource::fill(), Validation class             | Request sınıfında `rules()` tanımla                                  |
| **File Upload**   | MIME/ext validation, safe naming               | `upload()` helper kullan, direct `$_FILES` işleme                    |
| **Secrets**       | -                                              | `.env` commit etme, `Defines.php` secret'leri production'da değiştir |
| **Headers**       | -                                              | Apache/Nginx'te security headers (HSTS, CSP, X-Frame-Options)        |
| **HTTPS**         | -                                              | Production'da TLS terminasyonu, cookie secure=true                   |
| **Rate Limit**    | RateLimit middleware                           | Hassas endpoint'lerde custom limit tanımla                           |

---

## 🔐 Production Secret Yönetimi

**`Defines.php` içindeki şu değerler production'da MUTLAKA değiştirilmeli:**

```php
'jwt' => [
    'secret' => '...',  // 64+ char random hex
],
'crypt' => [
    'secret' => '...',  // 64 char hex (AES key)
],
'session' => [
    'encryption_key' => '...',  // 64 char hex
],
'cookie' => [
    'encryption_key' => '...',  // 64 char hex
],
```

**Üretim:**

```bash
# Linux/macOS
openssl rand -hex 32  # 64 char hex

# PHP
bin2hex(random_bytes(32))
```

---

> **Özet:** Framework **katmanlı güvenlik** sağlar: JWT auth, Gate/Policy authZ, Validation, Prepared statements, Encrypted session/cookie, Rate limiting, Secure file upload. Geliştiricinin görevi: **Policy check yapmak, validation kuralları tanımlamak, raw SQL yazmamak, secret'ları yönetmek, production hardening uygulamak.**
