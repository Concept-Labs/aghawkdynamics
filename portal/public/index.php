<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Config;
use App\Core\Router;

Config::load(__DIR__ . '/../config/config.php');

$router = new Router();
$router->dispatch();
