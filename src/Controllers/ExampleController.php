<?php

namespace App\Controllers;

use Orkestra\Services\Http\Controllers\BaseHtmlController;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ExampleController extends BaseHtmlController
{
	public function __invoke(ServerRequestInterface $request, array $args): ResponseInterface
	{
		return $this->render('index');
	}
}
