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


use DoctrineProxies\__CG__\Voetbal\PoulePlace;
use Voetbal\External\System\Importable\Competition as CompetitionImportable;
use Voetbal\External\System\Importable\Structure as StructureImportable;
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

    $externalSystemRepos = $em->getRepository( \Voetbal\External\System::class );
    $competitionRepos = $em->getRepository( \Voetbal\Competition::class );
    $competitionService = $voetbal->getService( \Voetbal\Competition::class );
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
            if( $externalSystem === null or ( $externalSystem instanceof StructureImportable ) !== true ) {
                continue;
            }
            $externalSystem->init();
            foreach( $competitions as $competition ) {
                if( $competition->getFirstRound() !== null ) {
                    continue;
                }
                $externalCompetition = $externalCompetitionRepos->findOneByImportable( $externalSystemBase, $competition );
                if( $externalCompetition === null or strlen($externalCompetition->getExternalId()) === null ) {
                    $logger->addNotice('for comopetition '.$competition->getName().' there is no "'.$externalSystemBase->getName().'"-competition available' );
                    continue;
                }
                $externalSystemHelper = $externalSystem->getStructureImporter(
                    $externalSystem->getCompetitionImporter(
                        $competitionService,
                        $competitionRepos,
                        $externalCompetitionRepos
                    ),
                    $externalSystem->getTeamImporter(
                        $competitionService,
                        $competitionRepos,
                        $externalCompetitionRepos
                    ),
                    $externalTeamRepos
                );
                $externalSystemHelper->create( $competition, $externalCompetition );
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


