# Codebase Guidelines

> **Purpose:** This document defines the coding standards, best practices, and "how-to" guides for development. All code must adhere to these rules.

## 1. General Principles (Tier 0 Rules)

### Clean Code & SOLID

- **Single Responsibility**: A class should have one job. (e.g., Controllers only handle HTTP).
- **Dependency Injection**: Dependencies must be injected via constructor, not created with `new`.
- **DRY (Don't Repeat Yourself)**: Use Helper functions or Base classes for repeated logic.
- **Strict Types**: All PHP files MUST start with `declare(strict_types=1);`.

### Naming Conventions

- **Classes**: `PascalCase` (e.g., `UserProfileController`)
- **Methods/Variables**: `camelCase` (e.g., `getUserProfile`)
- **Constants**: `UPPER_SNAKE_CASE` (e.g., `MAX_RETRY_COUNT`)
- **Database Tables**: `snake_case` (e.g., `app_user_profiles`)
- **Routes**: `kebab-case` (e.g., `/api/v1/user-profile`)

## 2. Development Guides

### How to Create a New Module

1. **Scaffold**: Create folder `App/Modules/[Name]`.
2. **Files**: Create Controller, Service, Repository, Policy, Request, Response.
3. **Route**: Register in `App/Routes/Routes.php` under `api/v1/[name]`.
4. **Migration**: Create table `app_[name]` and insert permissions into `app_permissions`.

### How to Handle Errors

- **Validation**: Throw `SystemException` with code `400`.
- **Not Found**: Throw `SystemException` with code `404`.
- **Unauthorized**: Gate throws `403` automatically.
- **Global Handler**: The framework catches exceptions and returns uniform JSON:
    ```json
    { "status": false, "message": "Error description" }
    ```

### How to Use Dependency Injection

- **Autowiring**: The container automatically resolves type-hinted dependencies.
    ```php
    public function __construct(protected UserService $service) {}
    ```
- **Binding**: If you need a specific implementation, bind it in `config/defines.php` (Providers).

## 3. Layer Rules (Strict)

### Controller

- ✅ **DO**: Call `Gate::authorize`.
- ✅ **DO**: Return `$this->response->json()`.
- ❌ **DON'T**: Write SQL queries.
- ❌ **DON'T**: Perform complex logic/calculations.

### Service

- ✅ **DO**: Use `$this->repository` for data.
- ✅ **DO**: Use `$this->validate()` for input check.
- ✅ **DO**: Use `$this->check()` for duplicate check.
- ❌ **DON'T**: Return HTTP responses directly.

### Repository

- ✅ **DO**: Return raw arrays or objects.
- ❌ **DON'T**: Validate data.
- ❌ **DON'T**: Throw HTTP exceptions (return empty/false instead).

## 4. Security Checklist

- [ ] **Rate Limiting**: Apply `RateLimit` middleware to public auth endpoints.
- [ ] **Authorization**: Every controller method MUST have a permission check.
- [ ] **Validation**: Never trust user input. Validate everything in Service layer.
- [ ] **SQL Injection**: Always use Query Builder bindings (`prepare`/`execute`), never raw SQL string concatenation.
