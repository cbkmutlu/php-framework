# Booking SaaS PHP Framework - Kullanım Dokümantasyonu

Bu dokümantasyon, Booking SaaS projesinin `backend/` dizini altında bulunan özel PHP framework'ünün mimarisini, çalışma prensiplerini ve geliştirme yöntemlerini açıklar.

## 📋 İçerik Haritası

| Belge                                          | Açıklama                                                                   |
| ---------------------------------------------- | -------------------------------------------------------------------------- |
| [`architecture.md`](architecture.md)           | Framework mimarisi, katmanlar ve HTTP request yaşam döngüsü                |
| [`getting-started.md`](getting-started.md)     | Kurulum, başlangıç ve temel kavramlar                                      |
| [`development-guide.md`](development-guide.md) | Günlük geliştirme: modül, route, controller, service, repository oluşturma |
| [`configuration.md`](configuration.md)         | Environment, konfigürasyon dosyaları ve çalışma ortamları                  |
| [`database.md`](database.md)                   | Veritabanı erişimi, migration, seeder ve repository pattern                |
| [`security.md`](security.md)                   | Authentication, authorization, validation ve güvenlik mekanizmaları        |
| [`cli.md`](cli.md)                             | CLI komutları ve framework araçları                                        |

## 🎯 Framework'e Genel Bakış

Booking SaaS PHP Framework, **API tabanlı, modüler monolit** mimarisinde bir backend çerçevesidir. Temel özellikleri:

- **PHP 8.2+** gerektirir
- **PSR-4** autoloading (`App\\` ve `System\\` namespace'leri)
- **Container tabanlı** dependency injection
- **JWT tabanlı** stateless authentication (access + refresh token rotation)
- **Role/Permission tabanlı** authorization (scope destekli)
- **Soft delete** ve audit field'ları (`created_at`, `updated_at`, `deleted_at`, `created_by`, `updated_by`, `deleted_by`) ile veri bütünlüğü
- **Swagger/OpenAPI** entegrasyonu (annotation tabanlı)
- **Çoklu dil** desteği (IntlDateFormatter ile)
- **Dosya yükleme** (local + Cloudflare R2/S3 uyumlu)
- **Rate limiting**, **CSRF**, **Security** middleware'leri
- **APCu** destekli cache (fallback: file cache)

## 📁 Dizin Yapısı

```
backend/
├── App/                    # Uygulama katmanı (sizin kodunuz)
│   ├── Config/             # Konfigürasyon dosyaları
│   ├── Core/               # Uygulama çekirdeği (Abstracts, Middlewares, Handlers...)
│   ├── Modules/            # Feature modülleri (Auth, Product, User, vb.)
│   ├── Routes/             # Route tanımları
│   ├── Migrations/         # Veritabanı migration'ları
│   ├── Seeds/              # Veritabanı seed'leri
│   └── Storage/            # Log, cache, upload (runtime)
├── System/                 # Framework çekirdeği (Container, Router, Database, Http, Jwt, vb.)
│   └── Helpers/            # Global helper fonksiyonları
├── Public/                 # Web root (index.php, assets, swagger, uploads)
├── vendor/                 # Composer bağımlılıkları
├── composer.json
├── cli                     # CLI entry point
└── Dockerfile
```

## ⚡ Hızlı Başlangıç

```bash
# Bağımlılıkları yükle
composer install

# Environment dosyasını kopyala ve düzenle
cp .env.development .env

# Veritabanı migration'larını çalıştır (CLI üzerinden)
php cli migrate

# Seed verilerini yükle
php cli seed

# Development sunucusunu başlat
php -S localhost:8000 -t Public
```

## 🔑 Temel Kurallar

1. **Framework çekirdeği (`System/`) değiştirilmez** - Sadece `App/` katmanında geliştirme yapılır
2. **Her modül kendi sorumluluğunda** - Controller, Service, Repository, Request, Response ayrılır
3. **Dependency Injection** - Constructor injection ile container'dan alınır
4. **Validation** - Request sınıflarında (`Resource` trait'i ile) tanımlanır
5. **Soft Delete** - Varsayılan davranış, `deleted_at` kontrolü Repository base'inde yapılır
6. **Audit Fields** - Migration'larda `defaults()` metodu ile otomatik eklenir
7. **API Prefix** - Tüm endpoint'ler `/api/v1/` ile başlar

## 🔗 İlgili Kaynaklar

- [Proje kök AGENTS.md](../../../AGENTS.md) - Genel proje kuralları
- [Backend AGENTS.md](../AGENTS.md) - Backend geliştirme kuralları
- [Ürün dokümantasyonu](../../../docs/product/) - MVP kapsamı, workflow'lar
- [Mimari dokümantasyonu](../../../docs/architecture/) - Sistem mimarisi, API spec

---

> **Not:** Bu framework özel geliştirilmiş bir çerçevedir. `vendor/` altındaki üçüncü taraf paketler (filp/whoops, zircote/swagger-php, phpmailer, doctrine/annotations, aws/aws-sdk-php, egulias/email-validator) framework'ün **iç API'si değildir**; framework bu paketleri `System\*` sınıfları üzerinden sarar (wrapper pattern).
