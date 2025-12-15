<?php

declare(strict_types=1);

define('ENV', 'development');
define('ROOT_DIR', realpath(__DIR__ . '/../../') . '/');
define('APP_DIR', ROOT_DIR . 'App/');
define('SYSTEM_DIR', ROOT_DIR . 'System/');
define('PUBLIC_DIR', ROOT_DIR . 'Public/');
define('BASE_URL', rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/'));
