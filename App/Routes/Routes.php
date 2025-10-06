<?php

declare(strict_types=1);

use App\Core\Middlewares\Auth;
use App\Modules\Image\ImageController;
use App\Modules\Product\ProductController;
use App\Modules\Category\CategoryController;

/** @var System\Router\Router $router */

// Image routes for image_path
$router->prefix('v1/image')->middleware([Auth::class])->group(function () use ($router) {
   $router->post('/create', [ImageController::class, 'uploadImage']);
   $router->post('/delete', [ImageController::class, 'deleteImage']);
});

// Product routes
$router->prefix('v1/product')->middleware([Auth::class])->group(function () use ($router) {
   $router->get('/', [ProductController::class, 'getAllProduct']);
   $router->post('/', [ProductController::class, 'createProduct']);
   $router->put('/', [ProductController::class, 'updateProduct']);
   $router->delete('/{id}', [ProductController::class, 'deleteProduct'])->where(['id' => '([0-9]+)']);
   $router->get('/{id}', [ProductController::class, 'getProduct'])->where(['id' => '([0-9]+)']);
});

// Category routes
$router->prefix('v1/category')->middleware([Auth::class])->group(function () use ($router) {
   $router->get('/', [CategoryController::class, 'getAllCategory']);
   $router->post('/', [CategoryController::class, 'createCategory']);
   $router->put('/', [CategoryController::class, 'updateCategory']);
   $router->delete('/{id}', [CategoryController::class, 'deleteCategory'])->where(['id' => '([0-9]+)']);
   $router->get('/{id}', [CategoryController::class, 'getCategoryById'])->where(['id' => '([0-9]+)']);
});
