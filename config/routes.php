<?php

declare(strict_types=1);

use App\Actions\BetLineAction;
use App\Actions\LayBackAction;
use App\Actions\Auth as AuthAction;
use App\Actions\BookmakerAction;
use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;
use App\Actions\Voetbal\AssociationAction;
use App\Actions\Voetbal\ExternalSystemAction;

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
        $group->group('/externalsystems', function ( Group $group ) {
            $group->options('', ExternalSystemAction::class . ':options');
            $group->post('', ExternalSystemAction::class . ':add');
            $group->get('', ExternalSystemAction::class . ':fetch');
            $group->options('/{id}', ExternalSystemAction::class . ':options');
            $group->get('/{id}', ExternalSystemAction::class . ':fetchOne');
            $group->put('/{id}', ExternalSystemAction::class . ':edit');
            $group->delete('/{id}', ExternalSystemAction::class . ':remove');
        });
    });
};