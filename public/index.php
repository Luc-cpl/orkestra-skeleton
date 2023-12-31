<?php

use Orkestra\Handlers\HttpHandler;

require_once __DIR__ . '/../bootstrap/app.php';

App\app()->boot();
App\app()->get(HttpHandler::class)->handle();