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
use Voetbal\Competition\Service as CompetitionService;
use Voetbal\Competition\Repository as CompetitionRepos;
use Voetbal\External\System as ExternalSystemBase;
use Voetbal\External\System\Factory as ExternalSystemFactory;
use Monolog\Logger;

$settings = $app->getContainer()->get('settings');
$em = $app->getContainer()->get('em');
$voetbal = $app->getContainer()->get('voetbal');

$logger = new Logger('cronjob-teams');
$logger->pushProcessor(new \Monolog\Processor\UidProcessor());
$logger->pushHandler(new \Monolog\Handler\StreamHandler($settings['logger']['cronjobpath'] . 'teams.log', $settings['logger']['level']));

try {
    $conn = $em->getConnection();
    $externalSystemRepos = $em->getRepository( \Voetbal\External\System::class );
    $teamRepos = $em->getRepository( \Voetbal\Team::class );
    $teamService = $voetbal->getService( \Voetbal\Team::class );
    $competitionRepos = $em->getRepository( \Voetbal\Competition::class );
    $externalTeamRepos = $em->getRepository( \Voetbal\External\Team::class );
    $externalCompetitionRepos = $em->getRepository( \Voetbal\External\Competition::class );
    $externalSystemFactory = new ExternalSystemFactory();

    $externalSystems = $externalSystemRepos->findAll();
    $competitions = $competitionRepos->findAll();
    foreach( $externalSystems as $externalSystemBase ) {
        echo $externalSystemBase->getName() . PHP_EOL;
        try {
            $externalSystem = $externalSystemFactory->create( $externalSystemBase );
            if( $externalSystem === null or ( $externalSystem instanceof TeamImportable ) !== true ) {
                continue;
            }
            $externalSystem->init();
            foreach( $competitions as $competition ) {
                $externalCompetition = $externalCompetitionRepos->findOneByImportable( $externalSystemBase, $competition );
                if( $externalCompetition === null or strlen($externalCompetition->getExternalId()) === null ) {
                    $logger->addNotice('for comopetition '.$competition->getName().' there is no "'.$externalSystemBase->getName().'"-competition available' );
                    continue;
                }
                $association = $externalCompetition->getImportableObject()->getLeague()->getAssociation();
                $externalSystemHelper = $externalSystem->getTeamImporter(
                    $teamService,
                    $teamRepos,
                    $externalTeamRepos
                );
                $teams = $externalSystemHelper->get( $externalCompetition );
                foreach( $teams as $externalSystemTeam ) {
                    $externalId = $externalSystemHelper->getId( $externalSystemTeam );
                    $externalTeam = $externalTeamRepos->findOneByExternalId( $externalSystemBase, $externalId );
                    $conn->beginTransaction();
                    try {
                        if( $externalTeam === null ) {
                            $team = $externalSystemHelper->create($association, $externalSystemTeam);
                        } else {
                            $externalSystemHelper->update( $externalTeam->getImportableObject(), $externalSystemTeam );
                        }
                        $conn->commit();
                    } catch( \Exception $error ) {
                        $logger->addNotice($externalSystemBase->getName().'"-team could not be created: ' . $error->getMessage() );
                        $conn->rollBack();
                        continue;
                    }
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
