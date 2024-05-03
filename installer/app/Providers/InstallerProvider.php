<?php

namespace Orkestra\Skeleton\Providers;

use Orkestra\App;
use Orkestra\Interfaces\ProviderInterface;
use Orkestra\Skeleton\Commands\InstallCommand;
use Orkestra\Skeleton\Maker\MakerData;

class InstallerProvider implements ProviderInterface
{
	public array $commands = [
		InstallCommand::class,
	];

	public function register(App $app): void
	{
		$app->singleton(MakerData::class, MakerData::class);
	}

	public function boot(App $app): void
	{
	}
}