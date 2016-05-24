<?php
if(version_compare(PHP_VERSION,'5.3.0','<'))  die('require PHP > 5.3.0 !');
if (file_exists('./data/conf/debug.lock') || !file_exists('./data/install.lock')) {
    define ('APP_DEBUG', true);
    define ('DB_DEBUG', true);
} else {
    define ('APP_DEBUG', false);
    define ('DB_DEBUG', false);
}
define('APP_PATH','./app/');
define("RUNTIME_PATH", "./data/runtime/");
require './ThinkPHP/ThinkPHP.php';
