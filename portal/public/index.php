<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\App;
use App\Core\Config;

/**
 @todo: move config to App
 */
Config::load(__DIR__ . '/../config/config.php');

$app = App::getInstance();
$app->run();
