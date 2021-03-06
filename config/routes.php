<?php

declare(strict_types=1);

use App\Actions\BetLineAction;
use App\Actions\LayBackAction;
use App\Actions\AuthAction;
use App\Actions\BookmakerAction;
use App\Actions\BetGameAction;
use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;
use App\Actions\Sports\SportAction;
use App\Actions\Sports\AssociationAction;
use App\Actions\Sports\LeagueAction;
use App\Actions\Sports\SeasonAction;
use App\Actions\Sports\CompetitionAction;
use App\Actions\Sports\CompetitorAction;
use App\Actions\Sports\StructureAction;
use App\Actions\ExternalSourceAction;
use App\Actions\AttacherAction;

return function (App $app): void {
    $app->group('/public', function (Group $group): void {
        $group->group('/auth', function (Group $group): void {
            $group->options('/login', AuthAction::class . ':options');
            $group->post('/login', AuthAction::class . ':login');
        });
    });

    $app->group('/auth', function (Group $group): void {
        $group->options('/validatetoken', AuthAction::class . ':options');
        $group->post('/validatetoken', AuthAction::class . ':validateToken');
    });

    $app->options('/betgames', BetGameAction::class . ':options');
    $app->post('/betgames', BetGameAction::class . ':fetch');

    $app->options('/betlines', BetLineAction::class . ':options');
    $app->get('/betlines', BetLineAction::class . ':fetch');
    $app->options('/betlines/{gameId}', BetLineAction::class . ':options');
    $app->get('/betlines/{gameId}', BetLineAction::class . ':fetch');

//    $app->get('/laybacks', LayBackAction::class . ':fetch');


    $app->group('/bookmakers', function (Group $group): void {
        $group->options('', BookmakerAction::class . ':options');
        $group->post('', BookmakerAction::class . ':add');
        $group->get('', BookmakerAction::class . ':fetch');
        $group->options('/{id}', BookmakerAction::class . ':options');
        $group->get('/{id}', BookmakerAction::class . ':fetchOne');
        $group->put('/{id}', BookmakerAction::class . ':edit');
        $group->delete('/{id}', BookmakerAction::class . ':remove');
    });

    $app->group('/externalsources', function (Group $group): void {
        $group->options('', ExternalSourceAction::class . ':options');
        $group->post('', ExternalSourceAction::class . ':add');
        $group->get('', ExternalSourceAction::class . ':fetch');
        $group->options('/{id}', ExternalSourceAction::class . ':options');
        $group->get('/{id}', ExternalSourceAction::class . ':fetchOne');
        $group->put('/{id}', ExternalSourceAction::class . ':edit');
        $group->delete('/{id}', ExternalSourceAction::class . ':remove');

        $group->group('/{id}/', function (Group $group): void {
            $group->options('sports', ExternalSourceAction::class . ':options');
            $group->get('sports', ExternalSourceAction::class . ':fetchSports');
        });
        $group->group('/{id}/', function (Group $group): void {
            $group->options('associations', ExternalSourceAction::class . ':options');
            $group->get('associations', ExternalSourceAction::class . ':fetchAssociations');
        });
        $group->group('/{id}/', function (Group $group): void {
            $group->options('seasons', ExternalSourceAction::class . ':options');
            $group->get('seasons', ExternalSourceAction::class . ':fetchSeasons');
        });
        $group->group('/{id}/', function (Group $group): void {
            $group->options('leagues', ExternalSourceAction::class . ':options');
            $group->get('leagues', ExternalSourceAction::class . ':fetchLeagues');
        });
        $group->group('/{id}/', function (Group $group): void {
            $group->options('competitions', ExternalSourceAction::class . ':options');
            $group->get('competitions', ExternalSourceAction::class . ':fetchCompetitions');
            $group->options('competitions/{competitionId}', ExternalSourceAction::class . ':options');
            $group->get('competitions/{competitionId}', ExternalSourceAction::class . ':fetchCompetition');
        });
        $group->group('/{id}/{competitionId}/', function (Group $group): void {
            $group->options('competitors', ExternalSourceAction::class . ':options');
            $group->get('competitors', ExternalSourceAction::class . ':fetchCompetitors');
        });
        $group->group('/{id}/', function (Group $group): void {
            $group->options('bookmakers', ExternalSourceAction::class . ':options');
            $group->get('bookmakers', ExternalSourceAction::class . ':fetchBookmakers');
        });
    });

    $app->group('/attachers', function (Group $group): void {
        $group->group('/{externalSourceId}/', function (Group $group): void {
            $group->group('sports', function (Group $group): void {
                $group->options('', AttacherAction::class . ':options');
                $group->post('', AttacherAction::class . ':addSport');
                $group->get('', AttacherAction::class . ':fetchSports');
                $group->options('/{id}', AttacherAction::class . ':options');
                $group->delete('/{id}', AttacherAction::class . ':removeSport');
            });
            $group->group('associations', function (Group $group): void {
                $group->options('', AttacherAction::class . ':options');
                $group->post('', AttacherAction::class . ':addAssociation');
                $group->get('', AttacherAction::class . ':fetchAssociations');
                $group->options('/{id}', AttacherAction::class . ':options');
                $group->delete('/{id}', AttacherAction::class . ':removeAssociation');
            });
            $group->group('seasons', function (Group $group): void {
                $group->options('', AttacherAction::class . ':options');
                $group->post('', AttacherAction::class . ':addSeason');
                $group->get('', AttacherAction::class . ':fetchSeasons');
                $group->options('/{id}', AttacherAction::class . ':options');
                $group->delete('/{id}', AttacherAction::class . ':removeSeason');
            });
            $group->group('leagues', function (Group $group): void {
                $group->options('', AttacherAction::class . ':options');
                $group->post('', AttacherAction::class . ':addLeague');
                $group->get('', AttacherAction::class . ':fetchLeagues');
                $group->options('/{id}', AttacherAction::class . ':options');
                $group->delete('/{id}', AttacherAction::class . ':removeLeague');
            });
            $group->group('competitions', function (Group $group): void {
                $group->options('', AttacherAction::class . ':options');
                $group->post('', AttacherAction::class . ':addCompetition');
                $group->get('', AttacherAction::class . ':fetchCompetitions');
                $group->options('/{id}', AttacherAction::class . ':options');
                $group->get('/{importableId}', AttacherAction::class . ':fetchCompetition');
                $group->delete('/{id}', AttacherAction::class . ':removeCompetition');
            });
            $group->group('competitors', function (Group $group): void {
                $group->options('', AttacherAction::class . ':options');
                $group->post('', AttacherAction::class . ':addCompetitor');
                // $group->options('/{competitionId}', AttacherAction::class . ':options');
                $group->get('/{competitionId}', AttacherAction::class . ':fetchCompetitors');
                $group->options('/{id}', AttacherAction::class . ':options');
                $group->delete('/{id}', AttacherAction::class . ':removeCompetitor');
            });
            $group->group('bookmakers', function (Group $group): void {
                $group->options('', AttacherAction::class . ':options');
                $group->post('', AttacherAction::class . ':addBookmaker');
                $group->get('', AttacherAction::class . ':fetchBookmakers');
                $group->options('/{id}', AttacherAction::class . ':options');
                $group->delete('/{id}', AttacherAction::class . ':removeBookmaker');
            });
        });
    });

    $app->group('/voetbal', function (Group $group): void {
        $group->group('/sports', function (Group $group): void {
            $group->options('', SportAction::class . ':options');
            $group->post('', SportAction::class . ':add');
            $group->get('', SportAction::class . ':fetch');
            $group->options('/{id}', SportAction::class . ':options');
            $group->get('/{id}', SportAction::class . ':fetchOne');
            $group->put('/{id}', SportAction::class . ':edit');
            $group->delete('/{id}', SportAction::class . ':remove');
        });
        $group->group('/associations', function (Group $group): void {
            $group->options('', AssociationAction::class . ':options');
            $group->post('', AssociationAction::class . ':add');
            $group->get('', AssociationAction::class . ':fetch');
            $group->options('/{id}', AssociationAction::class . ':options');
            $group->get('/{id}', AssociationAction::class . ':fetchOne');
            $group->put('/{id}', AssociationAction::class . ':edit');
            $group->delete('/{id}', AssociationAction::class . ':remove');
        });
        $group->group('/seasons', function (Group $group): void {
            $group->options('', SeasonAction::class . ':options');
            $group->post('', SeasonAction::class . ':add');
            $group->get('', SeasonAction::class . ':fetch');
            $group->options('/{id}', SeasonAction::class . ':options');
            $group->get('/{id}', SeasonAction::class . ':fetchOne');
            $group->put('/{id}', SeasonAction::class . ':edit');
            $group->delete('/{id}', SeasonAction::class . ':remove');
        });
        $group->group('/leagues', function (Group $group): void {
            $group->options('', LeagueAction::class . ':options');
            $group->post('', LeagueAction::class . ':add');
            $group->get('', LeagueAction::class . ':fetch');
            $group->options('/{id}', LeagueAction::class . ':options');
            $group->get('/{id}', LeagueAction::class . ':fetchOne');
            $group->put('/{id}', LeagueAction::class . ':edit');
            $group->delete('/{id}', LeagueAction::class . ':remove');
        });
        $group->group('/competitions', function (Group $group): void {
            $group->options('', CompetitionAction::class . ':options');
            $group->post('', CompetitionAction::class . ':add');
            $group->get('', CompetitionAction::class . ':fetch');
            $group->options('/{id}', CompetitionAction::class . ':options');
            $group->get('/{id}', CompetitionAction::class . ':fetchOne');
            $group->put('/{id}', CompetitionAction::class . ':edit');
            $group->delete('/{id}', CompetitionAction::class . ':remove');

            $group->group('/{id}/', function (Group $group): void {
                $group->options('structure', StructureAction::class . ':options');
                $group->get('structure', StructureAction::class . ':fetchOne');
            });
        });
        $group->group('/competitors', function (Group $group): void {
            $group->options('', CompetitorAction::class . ':options');
            $group->post('', CompetitorAction::class . ':add');
            $group->get('', CompetitorAction::class . ':fetch');
            $group->options('/{id}', CompetitorAction::class . ':options');
            $group->get('/{id}', CompetitorAction::class . ':fetchOne');
            $group->put('/{id}', CompetitorAction::class . ':edit');
            $group->delete('/{id}', CompetitorAction::class . ':remove');
        });
    });
};
