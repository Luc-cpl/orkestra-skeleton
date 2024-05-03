<?php

namespace App\Controllers;

use Orkestra\Services\Http\Controllers\AbstractHtmlController;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ExampleController extends AbstractHtmlController
{
	public function __invoke(ServerRequestInterface $request, array $args): ResponseInterface
	{
		return $this->render('index');
	}
}
