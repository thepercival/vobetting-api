<?php

declare(strict_types=1);

use App\Actions\BetLineAction;
use App\Actions\LayBackAction;
use App\Actions\AuthAction;
use App\Actions\BookmakerAction;
use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;
use App\Actions\Voetbal\SportAction;
use App\Actions\Voetbal\AssociationAction;
use App\Actions\Voetbal\LeagueAction;
use App\Actions\Voetbal\SeasonAction;
use App\Actions\Voetbal\CompetitionAction;
use App\Actions\ExternalSourceAction;
use App\Actions\AttacherAction;

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

    $app->group('/externalsources', function ( Group $group ) {
        $group->options('', ExternalSourceAction::class . ':options');
        $group->post('', ExternalSourceAction::class . ':add');
        $group->get('', ExternalSourceAction::class . ':fetch');
        $group->options('/{id}', ExternalSourceAction::class . ':options');
        $group->get('/{id}', ExternalSourceAction::class . ':fetchOne');
        $group->put('/{id}', ExternalSourceAction::class . ':edit');
        $group->delete('/{id}', ExternalSourceAction::class . ':remove');
        $group->group('/{id}/', function ( Group $group ) {
            $group->options('sports', ExternalSourceAction::class . ':options');
            $group->get('sports', ExternalSourceAction::class . ':fetchSports');
        });
        $group->group('/{id}/', function ( Group $group ) {
            $group->options('associations', ExternalSourceAction::class . ':options');
            $group->get('associations', ExternalSourceAction::class . ':fetchAssociations');
        });
        $group->group('/{id}/', function ( Group $group ) {
            $group->options('seasons', ExternalSourceAction::class . ':options');
            $group->get('seasons', ExternalSourceAction::class . ':fetchSeasons');
        });
        $group->group('/{id}/', function ( Group $group ) {
            $group->options('leagues', ExternalSourceAction::class . ':options');
            $group->get('leagues', ExternalSourceAction::class . ':fetchLeagues');
        });
        $group->group('/{id}/', function ( Group $group ) {
            $group->options('competitions', ExternalSourceAction::class . ':options');
            $group->get('competitions', ExternalSourceAction::class . ':fetchCompetitions');
        });
    });

    $app->group('/attachers', function ( Group $group ) {
        $group->group('/{externalSourceId}/', function ( Group $group ) {            
            $group->group('sports', function ( Group $group ) {
                $group->options('', AttacherAction::class . ':options');
                $group->post('', AttacherAction::class . ':addSport');
                $group->get('', AttacherAction::class . ':fetchSports');
                $group->options('/{id}', AttacherAction::class . ':options');
                $group->delete('/{id}', AttacherAction::class . ':removeSport');
            });
            $group->group('associations', function ( Group $group ) {
                $group->options('', AttacherAction::class . ':options');
                $group->post('', AttacherAction::class . ':addAssociation');
                $group->get('', AttacherAction::class . ':fetchAssociations');
                $group->options('/{id}', AttacherAction::class . ':options');
                $group->delete('/{id}', AttacherAction::class . ':removeAssociation');
            });
            $group->group('seasons', function ( Group $group ) {
                $group->options('', AttacherAction::class . ':options');
                $group->post('', AttacherAction::class . ':addSeason');
                $group->get('', AttacherAction::class . ':fetchSeasons');
                $group->options('/{id}', AttacherAction::class . ':options');
                $group->delete('/{id}', AttacherAction::class . ':removeSeason');
            });
            $group->group('leagues', function ( Group $group ) {
                $group->options('', AttacherAction::class . ':options');
                $group->post('', AttacherAction::class . ':addLeague');
                $group->get('', AttacherAction::class . ':fetchLeagues');
                $group->options('/{id}', AttacherAction::class . ':options');
                $group->delete('/{id}', AttacherAction::class . ':removeLeague');
            });
            $group->group('competitions', function ( Group $group ) {
                $group->options('', AttacherAction::class . ':options');
                $group->post('', AttacherAction::class . ':addCompetition');
                $group->get('', AttacherAction::class . ':fetchCompetitions');
                $group->options('/{id}', AttacherAction::class . ':options');
                $group->delete('/{id}', AttacherAction::class . ':removeCompetition');
            });
        });
    });

    $app->group('/voetbal', function ( Group $group ) {
        $group->group('/sports', function ( Group $group ) {
            $group->options('', SportAction::class . ':options');
            $group->post('', SportAction::class . ':add');
            $group->get('', SportAction::class . ':fetch');
            $group->options('/{id}', SportAction::class . ':options');
            $group->get('/{id}', SportAction::class . ':fetchOne');
            $group->put('/{id}', SportAction::class . ':edit');
            $group->delete('/{id}', SportAction::class . ':remove');
        });
        $group->group('/associations', function ( Group $group ) {
            $group->options('', AssociationAction::class . ':options');
            $group->post('', AssociationAction::class . ':add');
            $group->get('', AssociationAction::class . ':fetch');
            $group->options('/{id}', AssociationAction::class . ':options');
            $group->get('/{id}', AssociationAction::class . ':fetchOne');
            $group->put('/{id}', AssociationAction::class . ':edit');
            $group->delete('/{id}', AssociationAction::class . ':remove');
        });
        $group->group('/seasons', function ( Group $group ) {
            $group->options('', SeasonAction::class . ':options');
            $group->post('', SeasonAction::class . ':add');
            $group->get('', SeasonAction::class . ':fetch');
            $group->options('/{id}', SeasonAction::class . ':options');
            $group->get('/{id}', SeasonAction::class . ':fetchOne');
            $group->put('/{id}', SeasonAction::class . ':edit');
            $group->delete('/{id}', SeasonAction::class . ':remove');
        });
        $group->group('/leagues', function ( Group $group ) {
            $group->options('', LeagueAction::class . ':options');
            $group->post('', LeagueAction::class . ':add');
            $group->get('', LeagueAction::class . ':fetch');
            $group->options('/{id}', LeagueAction::class . ':options');
            $group->get('/{id}', LeagueAction::class . ':fetchOne');
            $group->put('/{id}', LeagueAction::class . ':edit');
            $group->delete('/{id}', LeagueAction::class . ':remove');
        });
        $group->group('/competitions', function ( Group $group ) {
            $group->options('', CompetitionAction::class . ':options');
            $group->post('', CompetitionAction::class . ':add');
            $group->get('', CompetitionAction::class . ':fetch');
            $group->options('/{id}', CompetitionAction::class . ':options');
            $group->get('/{id}', CompetitionAction::class . ':fetchOne');
            $group->put('/{id}', CompetitionAction::class . ':edit');
            $group->delete('/{id}', CompetitionAction::class . ':remove');
        });
    });
};