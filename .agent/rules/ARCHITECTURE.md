# Project Architecture

> **Purpose:** This document outlines the fundamental design decisions, system components, and data flow of the application. It serves as the primary reference for understanding "how the system works".

## 1. System Overview

### Core Philosophy

The application is built on a custom PHP framework emphasizing **modularity**, **strict typing**, and **service-oriented architecture**.

- **Modular Design**: Business logic is encapsulated in self-contained modules (`App/Modules`).
- **Service Layer Pattern**: Controllers are thin; Services handle all business logic.
- **Security First**: Authorization is enforced at the controller level via Gate/Policy.

### Tech Stack

- **Language**: PHP 8.2+ (Strict Types enabled)
- **Database**: MySQL (via custom Query Builder)
- **Architecture**: MVC + Service + Repository
- **Auth**: JWT (Stateless)

## 2. Directory Structure & Map

| Directory           | Purpose              | Key Components                                                         |
| :------------------ | :------------------- | :--------------------------------------------------------------------- |
| **App/**            | **Business Logic**   | The heart of the application.                                          |
| ├── **Core/**       | Base Classes         | `Controller`, `Service`, `Repository`, `Model`, `Resource` (Abstracts) |
| ├── **Modules/**    | Features             | `Auth`, `User`, `Category`, `Role` (Modularized Logic)                 |
| ├── **Routes/**     | Endpoints            | `Routes.php` (Centralized Route Definitions)                           |
| ├── **Migrations/** | Database             | Version control for database schema.                                   |
| **System/**         | **Framework Kernel** | Core infrastructure components.                                        |
| ├── **Container/**  | DI                   | `Container.php` (Dependency Injection)                                 |
| ├── **Router/**     | Routing              | `Router.php` (Request Dispatcher)                                      |
| ├── **Database/**   | Data Access          | Custom Query Builder & Connection Manager                              |
| ├── **Gate/**       | Security             | Authorization logic & Permission caching                               |

## 3. High-Level Flows

### Request Life Cycle

1. **Entry**: `index.php` bootstraps the application.
2. **Routing**: `System\Router` matches URI to a Controller method.
3. **Middleware**: Global and Route-specific middlewares (e.g., `Auth`) intercept request.
4. **Controller**:
    - Instantiated by `System\Container`.
    - Dependencies (Services) are **autowired**.
    - Calls `Gate::authorize()` to check permissions.
5. **Service**:
    - Validates input (`$this->validate()`).
    - Executes business logic.
    - Calls Repository for data.
6. **Repository**: Executes SQL via Query Builder.
7. **Response**: JSON output sent back to client.

### Authentication & Authorization

- **Authentication**: Handled via **JWT**. Tokens are validated in `Auth` middleware.
- **Authorization**: Handled via **Gate** and **Policy**.
    - **Gate**: Loads user permissions from DB (cached file/memory).
    - **Policy**: Maps actions (e.g., `update`) to permissions (e.g., `product:update`).

## 4. Module Architecture (The Standard)

Every module in `App/Modules` MUST follow this structure:

| Component      | Responsibility                          | Naming Convention      |
| :------------- | :-------------------------------------- | :--------------------- |
| **Controller** | Handle Request, Auth Check, Response    | `[Name]Controller.php` |
| **Service**    | Validation, Business Logic, Transaction | `[Name]Service.php`    |
| **Repository** | Database Queries (CRUD)                 | `[Name]Repository.php` |
| **Request**    | Validation Rules                        | `[Name]Request.php`    |
| **Response**   | DTO / Output Formatting                 | `[Name]Response.php`   |
| **Policy**     | Permission Checks                       | `[Name]Policy.php`     |

## 5. Database Design

- **Migrations**: Located in `App/Migrations`.
- **Soft Deletes**: Supported via `softDelete` method in Repository (requires `deleted_at` column).
- **Timestamp**: Standard `created_at`, `updated_at`.
