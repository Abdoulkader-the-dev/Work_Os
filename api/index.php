<?php

define('LARAVEL_START', microtime(true));

$appRoot = dirname(__DIR__);

require $appRoot . '/vendor/autoload.php';

$app = require_once $appRoot . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
)->send();

$kernel->terminate($request, $response);
