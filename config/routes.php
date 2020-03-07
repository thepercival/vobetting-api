<?php

declare(strict_types=1);

use App\Actions\BetLineAction;
use App\Actions\LayBackAction;
use App\Actions\Auth as AuthAction;
use App\Actions\BookmakerAction;
use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;
use App\Actions\Voetbal\AssociationAction;
use App\Actions\Voetbal\ExternalSourceAction;

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
        $group->options('', BookmakerAction::class . ':options');
        $group->post('', BookmakerAction::class . ':add');
        $group->get('', BookmakerAction::class . ':fetch');
        $group->options('/{id}', BookmakerAction::class . ':options');
        $group->get('/{id}', BookmakerAction::class . ':fetchOne');
        $group->put('/{id}', BookmakerAction::class . ':edit');
        $group->delete('/{id}', BookmakerAction::class . ':remove');
    });

    $app->group('/voetbal', function ( Group $group ) {
        $group->group('/associations', function ( Group $group ) {
            $group->options('', AssociationAction::class . ':options');
            $group->post('', AssociationAction::class . ':add');
            $group->get('', AssociationAction::class . ':fetch');
            $group->options('/{id}', AssociationAction::class . ':options');
            $group->get('/{id}', AssociationAction::class . ':fetchOne');
            $group->put('/{id}', AssociationAction::class . ':edit');
            $group->delete('/{id}', AssociationAction::class . ':remove');
        });
        $group->group('/externalsources', function ( Group $group ) {
            $group->options('', ExternalSourceAction::class . ':options');
            $group->post('', ExternalSourceAction::class . ':add');
            $group->get('', ExternalSourceAction::class . ':fetch');
            $group->options('/{id}', ExternalSourceAction::class . ':options');
            $group->get('/{id}', ExternalSourceAction::class . ':fetchOne');
            $group->put('/{id}', ExternalSourceAction::class . ':edit');
            $group->delete('/{id}', ExternalSourceAction::class . ':remove');
        });
    });
};