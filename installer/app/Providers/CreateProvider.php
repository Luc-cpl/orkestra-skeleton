<?php

namespace Orkestra\Skeleton\Providers;

use Orkestra\App;
use Orkestra\Interfaces\ProviderInterface;
use Orkestra\Skeleton\Commands\CreateCommand;
use Orkestra\Skeleton\Maker\MakerData;

class CreateProvider implements ProviderInterface
{
	public array $commands = [
		CreateCommand::class,
	];

	public function register(App $app): void
	{
	}

	public function boot(App $app): void
	{
	}
}