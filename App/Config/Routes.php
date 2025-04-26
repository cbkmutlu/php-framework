<?php

declare(strict_types=1);

use System\Exception\ExceptionHandler;
use System\Starter\Starter;

$route = Starter::router();

// swagger
// site.com/swagger
$route->prefix('swagger')->module('swagger')->group(function ($route) {
   $route->get('/', 'Swagger@view');
   $route->get('/json', 'Swagger@run');
});

// error
$route->error(function ($uri) {
   header('HTTP/1.1 404 Not Found');
   throw new ExceptionHandler("Route not found [{$uri}]");
   // $view->render('Error@error');
});

// user routes
// site.com/profile
$route->module('user')->middleware(['auth'])->group(function () use ($route) {
   $route->get('/profile', 'UserController@profile');
});

// test routes
// site.com/user/benchmark
$route->prefix('user')->module('user')->group(function () use ($route) {
   $route->get('/benchmark', 'UserController@benchmark');
   $route->get('/cache', 'UserController@cache');
   $route->get('/cookie', 'UserController@cookie');
   $route->get('/database', 'UserController@database');
   $route->post('/database', 'UserController@database');
   $route->get('/session', 'UserController@session');
   $route->post('/session', 'UserController@session');
   $route->get('/hash', 'UserController@hash');
   $route->post('/hash', 'UserController@hash');
   $route->get('/log', 'UserController@log');
   $route->post('/log', 'UserController@log');
   $route->get('/curl', 'UserController@curl');
   $route->post('/curl', 'UserController@curl');
   $route->get('/request', 'UserController@request');
   $route->post('/request', 'UserController@request');
   $route->put('/request', 'UserController@request');
   $route->delete('/request', 'UserController@request');
   $route->get('/upload', 'UserController@upload');
   $route->post('/upload', 'UserController@upload');
   $route->get('/validation', 'UserController@validation');
   $route->post('/validation', 'UserController@validation');
   $route->get('/pagination', 'UserController@pagination');
   $route->get('/image', 'UserController@image');
});