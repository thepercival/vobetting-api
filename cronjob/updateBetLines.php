<?php
/**
 * Created by PhpStorm.
 * User: coen
 * Date: 20-2-18
 * Time: 11:43
 */

require __DIR__ . '/../vendor/autoload.php';
$settings = require __DIR__ . '/../conf/settings.php';
$app = new \Slim\App($settings);
// Set up dependencies
require __DIR__ . '/../conf/dependencies.php';
require __DIR__ . '/mailHelper.php';

use Monolog\Logger;
use VOBetting\External\System\Factory as ExternalSystemFactory;
use VOBetting\BetLine;
use VOBetting\External\System\Importable\BetLine as BetLineImportable;

$settings = $app->getContainer()->get('settings');
$em = $app->getContainer()->get('em');
$voetbal = $app->getContainer()->get('voetbal');

$logger = new Logger('cronjob-betlines' );
$logger->pushProcessor(new \Monolog\Processor\UidProcessor());

try {
    $logger->pushHandler(new \Monolog\Handler\StreamHandler($settings['logger']['cronjobpath'] . 'betlines.log', $settings['logger']['level']));

    $externalSystemFactory = new ExternalSystemFactory( $voetbal, $logger, $em->getConnection() );
    $externalSystemRepos = $voetbal->getRepository( \Voetbal\External\System::class );

    $maxDaysBeforeImport = 14;
    $externalSystemRepos = $voetbal->getRepository( \Voetbal\External\System::class );
    $leagueRepos = $voetbal->getRepository( \Voetbal\League::class );
    $competitionRepos = $voetbal->getRepository( \Voetbal\Competition::class );
    $externalLeagueRepos = $voetbal->getRepository( \Voetbal\External\League::class );
    $externalCompetitorRepos = $voetbal->getRepository( \Voetbal\External\Competitor::class );
    $gameRepos = $voetbal->getRepository( \Voetbal\Game::class );
    $betLineRepos = $voetbal->getRepository( \VOBetting\BetLine::class );
    $layBackRepos = $voetbal->getRepository( \VOBetting\LayBack::class );


    $externalSystems = $externalSystemRepos->findAll();
    $leagues = $leagueRepos->findAll();
    $betType = BetLine::_MATCH_ODDS;
    foreach( $externalSystems as $externalSystemBase ) {
        echo $externalSystemBase->getName() . PHP_EOL;

        try {
            $externalSystem = $externalSystemFactory->create( $externalSystemBase );
            if( $externalSystem === null or ( $externalSystem instanceof BetLineImportable ) !== true ) {
                continue;
            }
            $externalSystem->init();
            $externalSystemHelper = $externalSystem->getBetLineImporter(
                $betLineRepos,
                $competitionRepos,
                $gameRepos,
                $externalCompetitorRepos,
                $layBackRepos,
                $logger
            );
            $externalSystemHelper->setMaxDaysBeforeImport( $maxDaysBeforeImport );

            foreach ($leagues as $league) {
                $externalLeague = $externalLeagueRepos->findOneByImportable($externalSystemBase, $league );
                if ($externalLeague === null) {
                    $logger->addNotice("external league not found for externalSystem " . $externalSystemBase->getName() . " and league " . $league->getName() );
                    continue;
                }
                $events = $externalSystemHelper->get($externalLeague);
                foreach ($events as $event) {
                    $externalSystemHelper->process($league, $event, $betType);
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
