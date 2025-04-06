<?php

use Orkestra\Providers\EncryptionServiceProvider;
use Orkestra\Providers\CommandsProvider;
use Orkestra\Providers\HooksProvider;
use Orkestra\Providers\HttpProvider;
use Orkestra\Providers\ViewProvider;

return [
	EncryptionServiceProvider::class,
	CommandsProvider::class,
	HooksProvider::class,
	HttpProvider::class,
	ViewProvider::class,
];
