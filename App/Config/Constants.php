<?php

declare(strict_types=1);

define('ENV', 'development');
define('DS', DIRECTORY_SEPARATOR);
define('BASE_DIR', implode('/', array_slice(explode('/', $_SERVER['SCRIPT_NAME']), 0, -1)));
define('ROOT_DIR', str_replace('Public', '', realpath(getcwd())));
define('APP_DIR', ROOT_DIR . DS . 'App' . DS);
define('SYSTEM_DIR', ROOT_DIR . DS . 'System' . DS);
define('PUBLIC_DIR', BASE_DIR . DS . 'Public' . DS);
define('SECOND', 1);
define('MINUTE', 60);
define('HOUR', 3600);
define('DAY', 86400);
define('WEEK', 604800);
define('MONTH', 2592000);
