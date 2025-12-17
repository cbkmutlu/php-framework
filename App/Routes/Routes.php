<?php

declare(strict_types=1);

use App\Core\Middlewares\Auth;
use App\Modules\Auth\AuthController;
use App\Modules\Brand\BrandController;
use App\Modules\File\FileController;
use App\Modules\Product\ProductController;
use App\Modules\Category\CategoryController;

/** @var System\Router\Router $router */

// Auth routes
$router->prefix('v1/auth')->group(function () use ($router) {
   $router->post('/login', [AuthController::class, 'login']);
   $router->post('/refresh', [AuthController::class, 'refresh']);
   $router->post('/logout', [AuthController::class, 'logout']);
   $router->post('/logoutall', [AuthController::class, 'logoutAll']);
});

// File routes
$router->prefix('v1/file')->middleware([Auth::class])->group(function () use ($router) {
   $router->post('/', [FileController::class, 'uploadFile']);
   $router->patch('/', [FileController::class, 'unlinkFile']);
});

// Product routes
$router->prefix('v1/product')->middleware([Auth::class])->group(function () use ($router) {
   $router->get('/', [ProductController::class, 'getAll']);
   $router->post('/', [ProductController::class, 'create']);
   $router->put('/', [ProductController::class, 'update']);
   $router->delete('/{id}', [ProductController::class, 'delete'])->where(['id' => '([0-9]+)']);
   $router->get('/{id}', [ProductController::class, 'getById'])->where(['id' => '([0-9]+)']);
   $router->post('/image', [ProductController::class, 'uploadImage']);
   $router->delete('/image/{image_id}', [ProductController::class, 'deleteImage'])->where(['image_id' => '([0-9]+)']);
});

// Category routes
$router->prefix('v1/category')->middleware([Auth::class])->group(function () use ($router) {
   $router->get('/', [CategoryController::class, 'getAll']);
   $router->post('/', [CategoryController::class, 'create']);
   $router->put('/', [CategoryController::class, 'update']);
   $router->delete('/{id}', [CategoryController::class, 'delete'])->where(['id' => '([0-9]+)']);
   $router->get('/{id}', [CategoryController::class, 'getById'])->where(['id' => '([0-9]+)']);
   $router->post('/image', [CategoryController::class, 'uploadImage']);
   $router->delete('/{id}/image', [CategoryController::class, 'deleteImage'])->where(['id' => '([0-9]+)']);
});

// Brand routes
$router->prefix('v1/brand')->middleware([Auth::class])->group(function () use ($router) {
   $router->get('/', [BrandController::class, 'getAll']);
   $router->post('/', [BrandController::class, 'create']);
   $router->put('/', [BrandController::class, 'update']);
   $router->delete('/{id}', [BrandController::class, 'delete'])->where(['id' => '([0-9]+)']);
   $router->get('/{id}', [BrandController::class, 'getById'])->where(['id' => '([0-9]+)']);
});

