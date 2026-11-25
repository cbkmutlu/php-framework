# PHP Framework

Bu dizin PHP framework'ünü içerir.

## 📚 Framework Kullanım Dokümantasyonu

Framework'ün mimarisi, geliştirme pattern'leri ve kullanım rehberleri şu dizindedir:

**[`docs/`](docs/)**

| Belge                                               | Açıklama                                                                                               |
| --------------------------------------------------- | ------------------------------------------------------------------------------------------------------ |
| [`README.md`](docs/README.md)                       | Giriş, içerik haritası ve hızlı bakış                                                                  |
| [`architecture.md`](docs/architecture.md)           | Mimari, katmanlar, HTTP request yaşam döngüsü                                                          |
| [`getting-started.md`](docs/getting-started.md)     | Kurulum, ilk modül oluşturma, test etme                                                                |
| [`development-guide.md`](docs/development-guide.md) | Günlük geliştirme: route, controller, service, repository, request/response, policy, migration, seeder |
| [`configuration.md`](docs/configuration.md)         | Environment, konfigürasyon dosyaları, development/production, container providers                      |
| [`database.md`](docs/database.md)                   | Database query builder, repository pattern, migration, seeder, ilişkiler                               |
| [`security.md`](docs/security.md)                   | JWT auth, Gate/Policy authorization, validation, SQL güvenliği, şifreleme, rate limiting               |
| [`cli.md`](docs/cli.md)                             | CLI komutları (migrate, seed), Swagger, debug araçları                                                 |

## 🚀 Hızlı Başlangıç

```bash
# 1. Bağımlılıkları yükle
composer install

# 2. Environment
cp .env.development .env
# .env dosyasını düzenleyin (DB credentials, vb.)

# 3. Veritabanı
php cli migrate
php cli seed

# 4. Development sunucu
php -S localhost:8000 -t Public
```

## 📁 Proje Yapısı

```
backend/
├── App/                    # Uygulama kodunuz (burada geliştirme yapılır)
│   ├── Config/             # Constants, Defines, Authorization, Approval
│   ├── Core/               # Abstract base classes, Middlewares, Handlers
│   ├── Modules/            # Feature modülleri (Auth, Product, User, vb.)
│   ├── Routes/             # Route tanımları
│   ├── Migrations/         # Veritabanı migration'ları
│   ├── Seeds/              # Seed verileri
│   └── Storage/            # Logs, Cache (runtime, gitignore)
├── System/                 # Framework çekirdeği (DEĞİŞTİRİLMEZ)
│   └── Helpers/            # Global helper fonksiyonları
├── Public/                 # Web root (index.php, assets, swagger, uploads)
├── vendor/                 # Composer dependencies
├── composer.json
├── cli                     # CLI entry point
└── Dockerfile
```

## ⚡ Temel Komutlar

```bash
# Migration çalıştır
php cli migrate

# Seed verilerini yükle
php cli seed

# Swagger UI
# Tarayıcıda: http://localhost:8000/api/swagger/

# Logları izle
tail -f App/Storage/Logs/Log_$(date +%Y-%m-%d).log
```

---

> **Not:** `System/` dizini framework çekirdeğidir ve **değiştirilmez**. Tüm uygulama geliştirmesi `App/` dizini altında yapılır. Yeni modül eklerken [`getting-started.md`](docs/getting-started.md) ve [`development-guide.md`](docs/development-guide.md) rehberlerini takip edin.
