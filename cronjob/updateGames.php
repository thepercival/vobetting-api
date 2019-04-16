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
use Voetbal\External\System\Importable\Game as GameImportable;

$settings = $app->getContainer()->get('settings');
$em = $app->getContainer()->get('em');
$voetbal = $app->getContainer()->get('voetbal');

$appUrl = reset( $settings['www']['urls'] );
$logger = new App\UrlLogger('cronjob-games', $appUrl );
$logger->pushProcessor(new \Monolog\Processor\UidProcessor());


// the action shoud be defined here, dependant of the type of error ofcourse
// parameters should not be included here

// no game found for externalsystemgame for certain externalsystem
$this->addNotice('game could not be found, check here if games are created at ' . $this->errorUrl . 'admin/games/' . $competition->getId()  );
// no externalgame found for a game and certain externalsystem
$this->addNotice('game could not be found for externalsystem "'.$this->externalSystemBase->getName().'" and gameid '.$game->getId().' for competition "' . $competition->getName(). '" for updating' );

// no competitor found externalsystemcompetitor and certain externalsystembase
$this->addNotice("homecompetitor could not be found for ".$this->externalSystemBase->getName()."-competitorid " . $externalSystemGame->homecompetitor );
// no competitor found for competition and externalsystemcompetitors(home and away)
$this->addNotice( $this->externalSystemBase->getName() . "-game could not be found for : " . $externalSystemGame->homecompetitor . " vs " . $externalSystemGame->awaycompetitor );


try {
    $logger->pushHandler(new \Monolog\Handler\StreamHandler($settings['logger']['cronjobpath'] . 'games.log', $settings['logger']['level']));

    $externalSystemFactory = new ExternalSystemFactory( $voetbal, $logger, $em->getConnection() );
    $externalSystemRepos = $voetbal->getRepository( \Voetbal\External\System::class );
    $competitionRepos = $voetbal->getRepository( \Voetbal\Competition::class );

    $externalSystems = $externalSystemRepos->findAll();
    foreach( $externalSystems as $externalSystemBase ) {
        echo $externalSystemBase->getName() . PHP_EOL;
        try {
            $externalSystem = $externalSystemFactory->create( $externalSystemBase );
            if( $externalSystem === null or ( $externalSystem instanceof GameImportable ) !== true ) {
                continue;
            }
            $externalSystem->init();
            $importer = $externalSystem->getGameImporter();
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