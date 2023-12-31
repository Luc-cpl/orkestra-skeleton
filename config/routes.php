<?php

use Orkestra\Services\Http\interfaces\RouterInterface;

use App\Controllers\ExampleController;

return function (RouterInterface $router): void {
    $router->get('/', ExampleController::class)->setDefinition(['title' => 'Home']);
};
