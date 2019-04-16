<?php
/**
 * Created by PhpStorm.
 * User: coen
 * Date: 6-3-18
 * Time: 14:43
 */

require __DIR__ . '/../vendor/autoload.php';
$settings = require __DIR__ . '/../conf/settings.php';
$app = new \Slim\App($settings);
// Set up dependencies
require __DIR__ . '/../conf/dependencies.php';
require __DIR__ . '/mailHelper.php';

use Monolog\Logger;
use Voetbal\External\System\Factory as ExternalSystemFactory;
use Voetbal\External\System\Importable\Structure as StructureImportable;

$settings = $app->getContainer()->get('settings');
$em = $app->getContainer()->get('em');
$voetbal = $app->getContainer()->get('voetbal');

$logger = new Logger('cronjob-competitors');
$logger->pushProcessor(new \Monolog\Processor\UidProcessor());

try {
    $logger->pushHandler(new \Monolog\Handler\StreamHandler($settings['logger']['cronjobpath'] . 'structures.log', $settings['logger']['level']));

    $externalSystemFactory = new ExternalSystemFactory( $voetbal, $logger, $em->getConnection() );
    $externalSystemRepos = $voetbal->getRepository( \Voetbal\External\System::class );
    $competitionRepos = $voetbal->getRepository( \Voetbal\Competition::class );

    $externalSystems = $externalSystemRepos->findAll();
    foreach( $externalSystems as $externalSystemBase ) {
        echo $externalSystemBase->getName() . PHP_EOL;
        try {
            $externalSystem = $externalSystemFactory->create( $externalSystemBase );
            if( $externalSystem === null or ( $externalSystem instanceof StructureImportable ) !== true ) {
                continue;
            }
            $externalSystem->init();
            $importer = $externalSystem->getStructureImporter();
            $importer->createByCompetitions( $competitionRepos->findAll() );

        } catch (\Exception $error) {
            $logger->addError("GENERAL ERROR: " . $error->getMessage() );
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