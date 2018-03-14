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
use Voetbal\External\System\Importable\Structure as StructureImportable;
use Voetbal\External\System\Importable\Team as TeamImportable;
use Voetbal\Round\Config\Service as RoundConfigService;
use Voetbal\Competition\Repository as CompetitionRepos;
use Voetbal\External\System as ExternalSystemBase;
use Voetbal\External\System\Factory as ExternalSystemFactory;
use Monolog\Logger;

use JMS\Serializer\Serializer;

$settings = $app->getContainer()->get('settings');
$em = $app->getContainer()->get('em');
$voetbal = $app->getContainer()->get('voetbal');

$logger = new Logger('cronjob-structures');
$logger->pushProcessor(new \Monolog\Processor\UidProcessor());
$logger->pushHandler(new \Monolog\Handler\StreamHandler($settings['logger']['cronjobpath'] . 'structures.log', $settings['logger']['level']));

try {
    $conn = $em->getConnection();
    $externalSystemRepos = $em->getRepository( \Voetbal\External\System::class );
    $structureService = $voetbal->getService( \Voetbal\Structure::class );
    $roundConfigService = $voetbal->getService( \Voetbal\Round\Config::class );
    $competitionRepos = $em->getRepository( \Voetbal\Competition::class );
    $competitionService = $voetbal->getService( \Voetbal\Competition::class );
    $teamService = $voetbal->getService( \Voetbal\Team::class );
    $teamRepos = $em->getRepository( \Voetbal\Team::class );
    $externalTeamRepos = $em->getRepository( \Voetbal\External\Team::class );
    $externalCompetitionRepos = $em->getRepository( \Voetbal\External\Competition::class );
    $externalSystemFactory = new ExternalSystemFactory();
    $poulePlaceService = $voetbal->getService( \Voetbal\PoulePlace::class );

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
            $competitionImporter = $externalSystem->getCompetitionImporter(
                $competitionService,  $competitionRepos, $externalCompetitionRepos
            );
            $teamImporter = $externalSystem->getTeamImporter(
                $teamService, $teamRepos, $externalTeamRepos
            );
            $externalSystemHelper = $externalSystem->getStructureImporter(
                $competitionImporter, $teamImporter, $externalTeamRepos, $structureService, $poulePlaceService, $roundConfigService
            );
            foreach( $competitions as $competition ) {
                if( $competition->getFirstRound() !== null ) {
                    continue;
                }
                $externalCompetition = $externalCompetitionRepos->findOneByImportable( $externalSystemBase, $competition );
                if( $externalCompetition === null or strlen($externalCompetition->getExternalId()) === null ) {
                    $logger->addNotice('for comopetition '.$competition->getName().' there is no "'.$externalSystemBase->getName().'"-competition available' );
                    continue;
                }
                $conn->beginTransaction();
                try {
                    $externalSystemHelper->create( $competition, $externalCompetition );
                    $conn->commit();
                } catch( \Exception $e ) {
                    $logger->addNotice('for "'.$externalSystemBase->getName().'"-competition '.$competition->getName(). ' structure not created: ' . $e->getMessage() );
                    $conn->rollBack();
                    continue;
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


