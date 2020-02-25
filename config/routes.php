<?php

declare(strict_types=1);

use App\Actions\BetLineAction;
use App\Actions\LayBackAction;
use App\Actions\Auth as AuthAction;
use App\Actions\BookmakerAction;
use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;

return function (App $app) {

    $app->group('/public', function ( Group $group ) {
        $group->group('/auth', function ( Group $group ) {
            $group->options('/login', AuthAction::class . ':options');
            $group->post('/login', AuthAction::class . ':login');
        });

    });

    $app->group('/auth', function ( Group $group ) {
        $group->options('/validatetoken', AuthAction::class . ':options');
        $group->post('/validatetoken', AuthAction::class . ':validateToken');
    });

//    $app->get('/betlines', BetLineAction::class . ':fetch');
//    $app->get('/laybacks', LayBackAction::class . ':fetch');

    $app->group('/bookmakers', function ( Group $group ) {
        $group->post('', BookmakerAction::class . ':add');
        $group->get('', BookmakerAction::class . ':fetch');
        $group->get('/{id}', BookmakerAction::class . ':fetchOne');
        $group->put('/{id}', BookmakerAction::class . ':edit');
        $group->delete('/{id}', BookmakerAction::class . ':remove');
    });
};