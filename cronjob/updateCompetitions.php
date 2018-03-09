<?php
/**
 * Created by PhpStorm.
 * User: coen
 * Date: 6-3-18
 * Time: 14:43
 */

namespace App\Cronjob;

require __DIR__ . '/../vendor/autoload.php';

$settings = require __DIR__ . '/../app/settings.php';
$app = new \Slim\App($settings);

// Set up dependencies
require __DIR__ . '/../app/dependencies.php';
require __DIR__ . '/mailHelper.php';


use Voetbal\External\System\Importable\Competition as CompetitionImportable;
use Voetbal\Competition\Service as CompetitionService;
use Voetbal\Competition\Repository as CompetitionRepos;
use Voetbal\External\System as ExternalSystemBase;
use Voetbal\External\System\Factory as ExternalSystemFactory;
use Monolog\Logger;

use JMS\Serializer\Serializer;

$settings = $app->getContainer()->get('settings');
$logger = $app->getContainer()->get('logger');
$em = $app->getContainer()->get('em');
$voetbal = $app->getContainer()->get('voetbal');

try {
    $conn = $em->getConnection();
    $externalSystemRepos = $em->getRepository( \Voetbal\External\System::class );
    $seasonRepos = $em->getRepository( \Voetbal\Season::class );
    $competitionService = $voetbal->getService( \Voetbal\Competition::class );
    $competitionRepos = $em->getRepository( \Voetbal\Competition::class );
    $externalLeagueRepos = $em->getRepository( \Voetbal\External\League::class );
    $externalSeasonRepos = $em->getRepository( \Voetbal\External\Season::class );
    $externalCompetitionRepos = $em->getRepository( \Voetbal\External\Competition::class );
    $externalSystemFactory = new ExternalSystemFactory();

    $externalSystems = $externalSystemRepos->findAll();
    $seasons = $seasonRepos->findAll();
    foreach( $externalSystems as $externalSystemBase ) {
        echo $externalSystemBase->getName() . PHP_EOL;
        try {
            $externalSystem = $externalSystemFactory->create( $externalSystemBase );
            if( $externalSystem === null or ( $externalSystem instanceof CompetitionImportable ) !== true ) {
                continue;
            }
            $externalSystem->init();
            foreach( $seasons as $season ) {
                $externalSeason = $externalSeasonRepos->findOneByImportable( $externalSystemBase, $season );
                if( $externalSeason === null or strlen($externalSeason->getExternalId()) === null ) {
                    $logger->addNotice('for season '.$season->getName().' there is no "'.$externalSystemBase->getName().'"-season available' );
                    continue;
                }
                $externalSystemHelper = $externalSystem->getCompetitionImporter(
                    $competitionService,
                    $competitionRepos,
                    $externalCompetitionRepos
                );
                $competitions = $externalSystemHelper->get( $externalSeason );
                foreach( $competitions as $externalSystemCompetition ) {
                    $externalLeague = $externalLeagueRepos->findOneByExternalId( $externalSystemBase, $externalSystemCompetition->league );
                    if( $externalLeague === null or strlen($externalLeague->getExternalId()) === null ) {
                        $logger->addNotice('for "'.$externalSystemBase->getName().'"-league '.($externalSystemCompetition->league). ' there is no league available' );
                        continue;
                    }
                    $externalCompetition = $externalCompetitionRepos->findOneByExternalId( $externalSystemBase, $externalSystemCompetition->id );
                    $conn->beginTransaction();
                    try {
                        if( $externalCompetition === null ) { // add and create structure
                            $league = $externalLeague->getImportableObject();
                            $competition = $externalSystemHelper->create($league, $season, $externalSystemCompetition);
                        }
                        else {
                            // maybe update something??
                        }
                        $conn->commit();
                    } catch( \Exception $e ) {
                        $logger->addNotice('for "'.$externalSystemBase->getName().'" league '.($externalSystemCompetition->league). ' could not be created: ' . $e->getMessage() );
                        $conn->rollBack();
                        continue;
                    }
                }
            }
        } catch (\Exception $e) {
            if( $settings->get('environment') === 'production') {
                mailAdmin( $e->getMessage() );
                $logger->addError("GENERAL ERROR: " . $e->getMessage() );
            } else {
                echo $e->getMessage() . PHP_EOL;
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


