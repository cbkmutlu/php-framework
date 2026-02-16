<?php

declare(strict_types=1);

use App\Core\Middlewares\{Auth, RateLimit};
use App\Modules\Auth\AuthController;
use App\Modules\Brand\BrandController;
use App\Modules\Category\CategoryController;
use App\Modules\File\FileController;
use App\Modules\Permission\PermissionController;
use App\Modules\Product\ProductController;
use App\Modules\Role\RoleController;
use App\Modules\User\UserController;

/** @var System\Router\Router $router */

// Public routes
$router->prefix('api/v1/auth')->middleware([RateLimit::class])->group(function () use ($router) {
   $router->post('/login', [AuthController::class, 'login']);
   $router->post('/refresh', [AuthController::class, 'refresh']);
});

// Auth routes
$router->prefix('api/v1/auth')->middleware([Auth::class])->group(function () use ($router) {
   $router->post('/logout', [AuthController::class, 'logout']);
   $router->post('/logoutall', [AuthController::class, 'logoutAll']);
});

// File routes
$router->prefix('api/v1/file')->middleware([Auth::class])->group(function () use ($router) {
   $router->post('/', [FileController::class, 'uploadFile']);
   $router->patch('/', [FileController::class, 'unlinkFile']);
});

// Product routes
$router->prefix('api/v1/product')->middleware([Auth::class])->group(function () use ($router) {
   $router->get('/', [ProductController::class, 'getAll']);
   $router->post('/', [ProductController::class, 'create']);
   $router->put('/', [ProductController::class, 'update']);
   $router->delete('/{id}', [ProductController::class, 'delete'])->where(['id' => '([0-9]+)']);
   $router->get('/{id}', [ProductController::class, 'getById'])->where(['id' => '([0-9]+)']);
   $router->post('/image', [ProductController::class, 'uploadImage']);
   $router->delete('/image/{imageId}', [ProductController::class, 'deleteImage'])->where(['imageId' => '([0-9]+)']);
});

// Category routes
$router->prefix('api/v1/category')->middleware([Auth::class])->group(function () use ($router) {
   $router->get('/', [CategoryController::class, 'getAll']);
   $router->post('/', [CategoryController::class, 'create']);
   $router->put('/', [CategoryController::class, 'update']);
   $router->delete('/{id}', [CategoryController::class, 'delete'])->where(['id' => '([0-9]+)']);
   $router->get('/{id}', [CategoryController::class, 'getById'])->where(['id' => '([0-9]+)']);
   $router->post('/image', [CategoryController::class, 'uploadImage']);
   $router->delete('/{id}/image', [CategoryController::class, 'deleteImage'])->where(['id' => '([0-9]+)']);
});

// Brand routes
$router->prefix('api/v1/brand')->middleware([Auth::class])->group(function () use ($router) {
   $router->get('/', [BrandController::class, 'getAll']);
   $router->post('/', [BrandController::class, 'create']);
   $router->put('/', [BrandController::class, 'update']);
   $router->delete('/{id}', [BrandController::class, 'delete'])->where(['id' => '([0-9]+)']);
   $router->get('/{id}', [BrandController::class, 'getById'])->where(['id' => '([0-9]+)']);
});

// Permission routes
$router->prefix('api/v1/permission')->middleware([Auth::class])->group(function () use ($router) {
   $router->get('/', [PermissionController::class, 'getAll']);
   $router->post('/', [PermissionController::class, 'create']);
   $router->put('/', [PermissionController::class, 'update']);
   $router->delete('/{id}', [PermissionController::class, 'delete'])->where(['id' => '([0-9]+)']);
   $router->get('/grouped', [PermissionController::class, 'getGrouped']);
   $router->get('/{id}', [PermissionController::class, 'getById'])->where(['id' => '([0-9]+)']);
});

// Role routes
$router->prefix('api/v1/role')->middleware([Auth::class])->group(function () use ($router) {
   $router->get('/', [RoleController::class, 'getAll']);
   $router->post('/', [RoleController::class, 'create']);
   $router->put('/', [RoleController::class, 'update']);
   $router->delete('/{id}', [RoleController::class, 'delete'])->where(['id' => '([0-9]+)']);
   $router->put('/{id}/permission', [RoleController::class, 'syncPermission'])->where(['id' => '([0-9]+)']);
   $router->post('/{id}/permission/{permissionId}', [RoleController::class, 'givePermission'])->where(['id' => '([0-9]+)', 'permissionId' => '([0-9]+)']);
   $router->delete('/{id}/permission/{permissionId}', [RoleController::class, 'revokePermission'])->where(['id' => '([0-9]+)', 'permissionId' => '([0-9]+)']);
   $router->get('/{id}', [RoleController::class, 'getById'])->where(['id' => '([0-9]+)']);
});

// User routes
$router->prefix('api/v1/user')->middleware([Auth::class])->group(function () use ($router) {
   $router->get('/', [UserController::class, 'getAll']);
   $router->post('/', [UserController::class, 'create']);
   $router->put('/', [UserController::class, 'update']);
   $router->delete('/{id}', [UserController::class, 'delete'])->where(['id' => '([0-9]+)']);
   $router->put('/{id}/role', [UserController::class, 'syncRole'])->where(['id' => '([0-9]+)']);
   $router->post('/{id}/role/{roleId}', [UserController::class, 'giveRole'])->where(['id' => '([0-9]+)', 'roleId' => '([0-9]+)']);
   $router->delete('/{id}/role/{roleId}', [UserController::class, 'revokeRole'])->where(['id' => '([0-9]+)', 'roleId' => '([0-9]+)']);
   $router->put('/{id}/permission', [UserController::class, 'syncPermission'])->where(['id' => '([0-9]+)']);
   $router->post('/{id}/permission/{permissionId}', [UserController::class, 'givePermission'])->where(['id' => '([0-9]+)', 'permissionId' => '([0-9]+)']);
   $router->delete('/{id}/permission/{permissionId}', [UserController::class, 'revokePermission'])->where(['id' => '([0-9]+)', 'permissionId' => '([0-9]+)']);
   $router->get('/{id}', [UserController::class, 'getById'])->where(['id' => '([0-9]+)']);
});
