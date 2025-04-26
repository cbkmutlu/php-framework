<?php

declare(strict_types=1);

define('DS', DIRECTORY_SEPARATOR);
define('BASE_DIR', implode('/', array_slice(explode('/', $_SERVER['SCRIPT_NAME']), 0, -1)));
define('ROOT_DIR', str_replace('Public', '', realpath(getcwd())));
define('APP_DIR', ROOT_DIR . DS . 'App' . DS);
define('SYSTEM_DIR', ROOT_DIR . DS . 'System' . DS);
define('CONTROLLER_DIR', APP_DIR . 'Controllers' . DS);
define('MODEL_DIR', APP_DIR . 'Models' . DS);
define('VIEW_DIR', APP_DIR . 'Views' . DS);
define('PUBLIC_DIR', BASE_DIR . DS . 'Public' . DS);
define('ENV', 'development');
define('TIMEZONE', 'Europe/Istanbul');
define('LOCALE', 'tr_TR');
define('SECOND', 1);
define('MINUTE', 60);
define('HOUR', 3600);
define('DAY', 86400);
define('WEEK', 604800);
define('MONTH', 2592000);
