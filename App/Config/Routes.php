<?php

declare(strict_types=1);

use System\Starter\Starter;
use System\View\View;

$route = Starter::router();
$view = new View();

// swagger routes
$route->get('/swagger', function () {
	require BASE_DIR . 'Public/swagger/index.html';
});
$route->prefix('swagger')->module('swagger')->group(function ($route) {
	return $route->get('/json', 'Swagger@run');
});

// error route
$route->error(function () use ($view) {
	header('HTTP/1.1 404 Not Found');
	$view->render('error@error');
});

// user routes
$route->prefix('user')->module('user')->group(function () use ($route) {
   $route->post('/login', 'LoginController@index');
});
$route->module('user')->middleware(['auth'])->group(function () use ($route) {
   $route->get('/profile', 'ProfileController@index');
});

// customer routes
$route->prefix('customer')->module('customer')->middleware(['auth'])->group(function () use ($route) {
   $route->get('/list', 'ListController@index');
   $route->get('/profile/{id}', 'ProfileController@index')->where(['id' => '(\d+)']);
   $route->post('/add', 'CommandController@add');
   $route->delete('/delete/{id}', 'CommandController@delete')->where(['id' => '(\d+)']);
   $route->put('/update', 'CommandController@update');
});

// sales routes
$route->prefix('sales')->module('sales')->middleware(['auth'])->group(function () use ($route) {
	$route->get('/list', 'SaleListController@index');
	$route->post('/add', 'SaleCommandController@add');
	$route->delete('/delete/{id}', 'SaleCommandController@delete')->where(['id' => '(\d+)']);
	$route->put('/update', 'SaleCommandController@update');
});



// $route->prefix('backend')->namespace('IEC')->group(function ($route) {
// 	$route->get('/', 'Dashboard@index');
// 	$route->get('/2', 'Dashboard@index');
// 	$route->get('/3', 'Dashboard@index');
// });

// Validation
// $data = [
// 	'username' => 'selam'
// ];
// $rule1 = ['username', 'Kullanıcı Adı', 'required|alpha'];
// $rule2 = ['username' => ['label' => 'Kullanıcı Adı', 'rules' => 'required|alpha']];
// $rule3 = [['username', 'Kullanıcı Adı', 'required|alpha']];

// $validate = Starter::validation();
// $validate->data($data);
// // $validate->getErr
// $validate->rules(...$rule1);
// $validate->rules($rule2);
// $validate->rules($rule3);

// if ($validate->validate()) {
// 	echo "Geçerli";
// }

// new DateTime("2015-02-28");
// DateTime Object
// (
//     [date] => 2015-02-28 00:00:00.000000
//     [timezone_type] => 3
//     [timezone] => Europe/Istanbul
// )


	// 	dd($date->getDate(Date::GENERIC));
	// 	dd($date->getDate(true));
	// 	dd($date->getDate());
	// 	// dd($language->get('@date', 'just', ['trrr', 'test']));
	// 	// dd($language->set('tr_TR')->get('@date', 'just', ['trrr', 'test']));
	// 	// dd($language->get('@date', 'just', ['trrr', 'test']));


		// 	// dd(get_lang($session));
	// 	// $session->delete('7572559ca86e781ba8fe8073a0b725c6');
	// 	// $session->save('hebele', 'tr-TR');
	// 	// dd($language->locale());
	// 	// $session->destroy();
	// 	// $_SESSION['hebele'] = 'tr-TR';
	// 	// $session->save('hebele', 'tr-TR');
	// 	// dd($_SESSION);
	// 	// dd(session_id());



		// 	// #1
	// 	// $db->prepare("SELECT * FROM user WHERE name=?");
	// 	// $result2 = $db->execute(['ismail']);
	// 	// dd($result2->getAll());

	// 	// $db->connect('ANKARAHOST');

	// 	// $db->connect('secondary')->prepare('SELECT * FROM hebele');
	// 	// $result = $db->execute();
	// 	// dd($result->getAll());

	// 	// #2
	// 	// $db->prepare("UPDATE user SET name=:name WHERE id=1");
	// 	// $result3 = $db->execute([':name' => 'mutlu2']);
	// 	// dd($result3->getAffectedRows());

	// 	// $test = $db->query('UPDATE user SET name="Mutlu" WHERE id=1');
	// 	// dd($test->getAffectedRows());

	// 	// #3
	// 	// $db->prepare("SELECT * FROM user WHERE name=:name");
	// 	// $result = $db->execute(['mutlu']);

	// 	// $db->prepare("SELECT * FROM user WHERE name=?");
	// 	// $db->bind(1, 'mutlu');
	// 	// $result = $db->execute();


// use System\Date\Date;

// $datetime = new DateTimeImmutable();
// Immutable çalışmaz
// $datetime->modify('+1 year');
// dd($datetime->format('Y-m-d H:i:s'));

// Immutable çalışır
// $modify = $datetime->modify('+1 year');
// dd($modify->format('Y-m-d H:i:s'));


// $format = 'Y-d-m';
// $from = date_create_from_format($format, '2017-05-11');
// $default = date_create($from->date_format());
// dd($default);

// $date = new Date();

// dd($date->getDate('Y-m-d'));
// dd($date->getDate());

// $date = new Date();
// dd($date->setDate(new DateTime('2017-05-11'))->addDay(1)->getDate());
// dd($date->setDate(new Date('2017-05-11'))->addDay(1)->getDate());
// dd($date->setDate('2017-05-11')->getHumanTime());
// dd($date->setDate(1494505279)->addDay(1)->getDate());
// dd($date->setDate('2017-05-11', 'Y-m-d')->addDay(1)->getDate());

// dd($date->setDate('now')->getDate(Date::GENERIC));

// dd($date->setDate('2025-01-15')->getMiliSecond());
// dd($date->setDate('2017-05-09', 'Y-d-m'));

// dd($date->now()->getDate());
// dd($date->now()->compareDates('2017-05-11', '2017-07-15'));
// $test = $date->now();
// $date = $date->setDate('2040-05-11');
// // dd($test->getDate());
// dd($date->compareDates('2017-05-11'));
// dd($date->setTimezone('Europe/Paris')->getHour());

// dd($date->setDate('01.01.2017 11:11')->getDate(Date::GENERIC));
// dd($date->setDate('07.01.2017')->getDate());

// dd(date_create_from_format("j-M-Y","15-Mart-2013"));

// $test = new DateTime('2017-06-11 11:11:11');
// setlocale(LC_TIME, 'tr_TR');
// date_default_timezone_set('Europe/Istanbul');
// dd($test->format('Y-F-d H:i:s'));

// dd($date->addDay(1)->getHour());
// // $date->setDate('2017-05-11');
// dd($date->getYear());
// dd($date->getMonth());
// dd($date->getMonthString());
// dd($date->getDay());
// dd($date->getDayString());
// dd($date->getMinute());
// dd($date->getSecond());
// dd($date->getMiliSecond());
// $date->setDay(0);
// dd(strtotime('now'));
// dd(time());
// dd((int) microtime(true));

// $date = new DateTimeImmutable();
// $date->setTimestamp($date->getTimestamp() + 1);
// // $date->setTimestamp(time());
// $date = $date->format('u');
// echo $date;
// çıktı
// 13-01-2025 23:23:04.429983

// new DateTime($this->timestamp);
// echo $date->now()->get();

// $dateString = '2025-01-13 16:57:18';
// $date = new DateTime($dateString);
// dd($date);

// $date = new DateTime(); // Geçerli tarih ve saat
// $dayName = $date->format('l'); // Haftanın günü (örneğin: Monday, Tuesday, vb.)
// echo ;

// echo $date->now()->get();

// if ($date->getYear() === '2025') {
// }

// $route->prefix('backend')->namespace('IEC')->get('/', 'Dashboard@index');
// $route->prefix('backend')->namespace('IEC')->get('/', 'Dashboard@index');
// $route->prefix('backend')->namespace('IEC')->get('/', 'Dashboard@index');
// $route->prefix('backend')->namespace('IEC')->get('/', 'Dashboard@index');

// $route->prefix('frontend')->namespace('frontend')->group(function ($route) {
// 	$route->get('/blog/{postId}', 'Home@index')->where(['postId' => '(\d+)'])->name('test2');
// 	$route->get('/blog/{postId}', 'Home@index')->where(['postId' => '(\d+)'])->name('test33');
// });
// $route->prefix('frontend')->namespace('frontend')->group(function ($route) {
// 	$route->get('/', 'Home@index')->name('test4');
// });

// echo $route->getUrl('hebele.test3');
// dd(url_origin());
// dd(url_origin());
// dd(url_protocol());
// echo $route->getUrl('test1', []);
// dd($route->getRoutes());

// domain ip
// $route->domain(['backend.local'])->ip(['94.55.236.113', '127.0.0.1'])->prefix('frontend')->namespace('frontend')->get('/', 'Home@index');
// $route->domain(['backend.local'])->ip(['94.55.236.113', '127.0.0.1'])->prefix('frontend')->namespace('frontend')->get('/link1', 'Home@index');

// where
// $route->prefix('frontend')->namespace('frontend')->get('/', 'Home@index');
// $route->namespace('frontend')->get('/blog/{postId}', 'Home@index')->where(['postId' => '(\d+)']);

// $route->namespace('frontend')->group(function ($route) {
// 	$route->get('/test', 'Home@index');
// });

// Route::namespace('frontend')->group(function () {
// 	Route::get('/', 'Home@index');
// });

// print_r($route);
// echo "<br>";
// echo "<br>";
// $route->namespace('frontend')->group(function ($route) {
// 	// $route->get('/', 'Home@index');
// 	$route->get('/test', 'Home@index');
// });

// $route->namespace('frontend')->get('/', 'Home@index');
// print_r($route);
// echo "<br>";
// echo "<br>";

// $route->ip('94.55.236.113')->prefix('frontendc')->namespace('frontend')->group(function ($route) {
// 	$route->get('/', 'Home@index');
// 	$route->get('/home', 'Home@index');
// });

// $route->ssl()->ip(['94.55.236.113', '192.168.0.1', '127.0.0.1'])->namespace('frontend')->group(function ($route) {
// 	$route->get('/', 'Home@index');
// 	$route->get('/home', 'Home@index');
// });

// $route->middleware(['auth'])->group(function ($route) {
// 	$route->get('/', 'Home@index');
// });



// $route->namespace('frontend')->group(function ($route) {
// 	$route->get('/test', 'Home@index');
// });

// $route->prefix('frontendb')->namespace('frontend')->group(function ($route) {
// 	$route->get('/', 'Home@index');
// 	$route->get('/home', 'Home@index');
// });

// $route->ssl()->namespace('frontend')->get('/', 'Home@index');
// $route->namespace('frontend')->get('/test', 'Home@index');

// print_r($route);
// echo "<br>";
// echo "<br>";

// $route->namespace('frontend')->get('/hebe', 'Home@index');
// print_r($route);
// echo "<br>";
// echo "<br>";

// $route->namespace('frontend')->get('/homa', 'Home@index');
// print_r($route);
// echo "<br>";
// echo "<br>";

// Route::prefix('backend')->namespace('backend')->middleware(['auth'])->group(function () {
// 	Route::get('/', 'Dashboard@index');
// 	Route::get('/dashboard', 'Dashboard@index');
// });
