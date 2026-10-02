<?php

declare(strict_types=1);

use App\Core\App;

require dirname(__DIR__) . '/app/Core/App.php';

$app = App::instance();

// Ensure runtime directories exist.
$logs = $app->path('storage') . '/logs';
$uploads = $app->path('uploads');
foreach ([$logs, $uploads] as $dir) {
    if (!is_dir($dir)) {
        @mkdir($dir, 0777, true);
    }
}

$debug = (bool)($app->config['app']['debug'] ?? false);
error_reporting($debug ? E_ALL : E_ERROR | E_PARSE);
ini_set('display_errors', $debug ? '1' : '0');
ini_set('log_errors', '1');
ini_set('error_log', $logs . '/app.log');

$app->boot();
$app->handle();