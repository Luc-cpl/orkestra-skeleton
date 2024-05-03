<?php

namespace App;

use Orkestra\App;
use Orkestra\Configuration;

require_once __DIR__ . '/../../vendor/autoload.php';

/**
 * The configuration singleton
 * It allow us to change the configuration
 * on the fly before the app is initialized
 */
function config()
{
	static $config = null;
	$configDir = __DIR__ . '/../config';
	$config = $config ?? new Configuration([
		...(require $configDir . '/app.php'),
	]);
	return $config;
}

/**
 * The app singleton
 */
function app()
{
	static $app = null;
	$app = $app ?? new App(config());
	return $app;
}

$configDir = __DIR__ . '/../config';
$providers = require $configDir . '/providers.php';

foreach ($providers as $provider) {
	app()->provider($provider);
}
