<?php
/**
 * Created by PhpStorm.
 * User: coen
 * Date: 6-3-18
 * Time: 14:43
 */

require __DIR__ . '/../vendor/autoload.php';
$settings = require __DIR__ . '/../app/settings.php';
$app = new \Slim\App($settings);
require __DIR__ . '/../app/dependencies.php';
require __DIR__ . '/mailHelper.php';

use Monolog\Logger;
use Voetbal\External\System\Factory as ExternalSystemFactory;
use Voetbal\External\System\Importable\Competition as CompetitionImportable;

$settings = $app->getContainer()->get('settings');
$em = $app->getContainer()->get('em');
$voetbal = $app->getContainer()->get('voetbal');

$logger = new Logger('cronjob-competitions');
$logger->pushProcessor(new \Monolog\Processor\UidProcessor());

try {
    $logger->pushHandler(new \Monolog\Handler\StreamHandler($settings['logger']['cronjobpath'] . 'competitions.log', $settings['logger']['level']));

    $externalSystemFactory = new ExternalSystemFactory( $voetbal, $logger, $em->getConnection() );

    $externalSystemRepos = $voetbal->getRepository( \Voetbal\External\System::class );
    $seasonRepos = $voetbal->getRepository( \Voetbal\Season::class );
    $leagueRepos = $voetbal->getRepository( \Voetbal\League::class );

    $externalSystems = $externalSystemRepos->findAll();
    foreach( $externalSystems as $externalSystemBase ) {
        echo $externalSystemBase->getName() . PHP_EOL;
        try {
            $externalSystem = $externalSystemFactory->create( $externalSystemBase );
            if( $externalSystem === null or ( $externalSystem instanceof CompetitionImportable ) !== true ) {
                continue;
            }
            $externalSystem->init();
            $importer = $externalSystem->getCompetitionImporter();
            $importer->createByLeaguesAndSeasons( $leagueRepos->findAll(), $seasonRepos->findAll());
        } catch (\Exception $e) {
            $logger->addError("GENERAL ERROR: " . $e->getMessage() );
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


