<?php
// FRONT CONTROLLER

define('ROOT', realpath(__DIR__ . '/..'));
define('APP', ROOT . '/app');

ini_set('display_errors', 1);
error_reporting(E_ALL);

mb_internal_encoding("UTF-8");
mb_regex_encoding("UTF-8");


// Автозагрузка
require_once ROOT . '/vendor/autoload.php';

use EvgputWeb\MVC\Core\Config;
use EvgputWeb\MVC\Core\Db;
use EvgputWeb\MVC\Core\Router;


// Загружаем конфигурацию
Config::loadConfig();

// Установка соединения с БД
Db::setConnection();

// Запускаем Router
Router::run();
