<?php
/**
 * Created by PhpStorm.
 * User: coen
 * Date: 6-3-18
 * Time: 22:28
 */

//loop door de externalobjects voor externalsystem x en compettion y
//
//haal per externalobject de teams op
//
//ga dan weer kijken als de teams moeten worden gesynced idem als met comps
//
//
//$unable = true;
////create structure and assign teams
//// $numberOfTeams
//// $numberOfGames
//// numberOfMatchdays
//if ( $unable ) {
//    throw new \Exception("unable to determine structure", E_ERROR );
//}

namespace App\Cronjob;

require __DIR__ . '/../vendor/autoload.php';

$settings = require __DIR__ . '/../app/settings.php';
$app = new \Slim\App($settings);

// Set up dependencies
require __DIR__ . '/../app/dependencies.php';
require __DIR__ . '/mailHelper.php';


use Voetbal\External\System\Importable\Team as TeamImportable;
use Voetbal\External\System\Importable\Game as GameImportable;
use Voetbal\External\System\Importable\Competition as CompetitionImportable;
use Voetbal\Competition\Service as CompetitionService;
use Voetbal\Competition\Repository as CompetitionRepos;
use Voetbal\External\System as ExternalSystemBase;
use Voetbal\External\System\Factory as ExternalSystemFactory;
use Monolog\Logger;
use Voetbal\Planning\Service as PlanningService;
use Voetbal\Game;

$settings = $app->getContainer()->get('settings');
$logger = $app->getContainer()->get('logger');
$em = $app->getContainer()->get('em');
$voetbal = $app->getContainer()->get('voetbal');

try {
    $conn = $em->getConnection();
    $externalSystemRepos = $em->getRepository( \Voetbal\External\System::class );
    $externalTeamRepos = $em->getRepository( \Voetbal\External\Team::class );
    $gameRepos = $em->getRepository( \Voetbal\Game::class );
    $teamRepos = $em->getRepository( \Voetbal\Team::class );
    $teamService = $voetbal->getService( \Voetbal\Team::class );
    $gameService = $voetbal->getService( \Voetbal\Game::class );
    $structureService = $voetbal->getService( \Voetbal\Structure::class );
    $competitionRepos = $em->getRepository( \Voetbal\Competition::class );
    $externalGameRepos = $em->getRepository( \Voetbal\External\Game::class );
    $externalCompetitionRepos = $em->getRepository( \Voetbal\External\Competition::class );
    $externalSystemFactory = new ExternalSystemFactory();
    $planningService = $voetbal->getService( \Voetbal\Planning::class );

    $externalSystems = $externalSystemRepos->findAll();
    $competitions = $competitionRepos->findAll();
    foreach( $externalSystems as $externalSystemBase ) {
        echo $externalSystemBase->getName() . PHP_EOL;
        try {
            $externalSystem = $externalSystemFactory->create( $externalSystemBase );
            if( $externalSystem === null or ( $externalSystem instanceof GameImportable ) !== true
                or ( $externalSystem instanceof CompetitionImportable ) !== true
                or ( $externalSystem instanceof TeamImportable ) !== true ) {
                continue;
            }
            $externalSystem->init();
            $externalSystemHelper = $externalSystem->getGameImporter(
                $gameService,
                $gameRepos,
                $externalGameRepos,
                $externalTeamRepos,
                $externalSystem->getTeamImporter(
                    $teamService,
                    $teamRepos,
                    $externalTeamRepos
                )
            );
            foreach( $competitions as $competition ) {
                $externalCompetition = $externalCompetitionRepos->findOneByImportable( $externalSystemBase, $competition );
                if( $externalCompetition === null or strlen($externalCompetition->getExternalId()) === null ) {
                    $logger->addNotice('for competition '.$competition->getName().' there is no "'.$externalSystemBase->getName().'"-competition available' );
                    continue;
                }

                $conn->beginTransaction();
                try {
                   $hasGames = $gameRepos->hasCompetitionGames( $competition );
                   if ( $hasGames === false ) {
                       $planningService->create($competition);
                       $externalSystemHelper->create($externalCompetition);
                   }
                   $hasUnfinishedGames = $gameRepos->hasCompetitionGames( $competition, Game::STATE_CREATED + Game::STATE_INPLAY );
                   if( $hasUnfinishedGames === true ) {
                       $externalSystemHelper->update($externalCompetition );
                   }
                   $conn->commit();
                } catch( \Exception $error ) {
                    $logger->addNotice($externalSystemBase->getName().'"-games could not be created or updated: ' . $error->getMessage() );
                    $conn->rollBack();
                }
            }
        } catch (\Exception $error) {
            if( $settings->get('environment') === 'production') {
                mailAdmin( $error->getMessage() );
                $logger->addError("GENERAL ERROR: " . $error->getMessage() );
            } else {
                echo $error->getMessage() . PHP_EOL;
            }
        }
    }
}
catch( \Exception $e ) {
    if( $settings->get('environment') === 'production') {
        mailAdmin( $e->getMessage() );
        $logger->addError("GENERAL ERROR: " . $e->getMessage() );
    } else {
        echo $e->getMessage() . PHP_EOL;
    }
}
