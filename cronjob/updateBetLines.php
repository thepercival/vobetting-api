<?php
/**
 * Created by PhpStorm.
 * User: coen
 * Date: 20-2-18
 * Time: 11:43
 */

namespace App\Cronjob;

require __DIR__ . '/../vendor/autoload.php';

$settings = require __DIR__ . '/../app/settings.php';
$app = new \Slim\App($settings);

// Set up dependencies
require __DIR__ . '/../app/dependencies.php';

$settings = $app->getContainer()->get('settings');
$em = $app->getContainer()->get('em');

$logger = new Logger('cronjob-betlines');
$logger->pushProcessor(new \Monolog\Processor\UidProcessor());
$logger->pushHandler(new \Monolog\Handler\StreamHandler($settings['logger']['cronjobpath'] . 'betlines.log', $settings['logger']['level']));

use Voetbal\Competition\Repository as CompetitionRepos;
use Voetbal\External\System as ExternalSystemBase;
use Voetbal\External\Team\Repository as ExternalTeamRepos;
use Voetbal\Game\Repository as GameRepos;
use VOBetting\BetLine\Repository as BetLineRepos;
use VOBetting\LayBack\Repository as LayBackRepos;
use VOBetting\BetLine;
use Monolog\Logger;
use VOBetting\External\System\Factory as ExternalSystemFactory;
use VOBetting\External\System\Importable\BetLine as BetLineImportable;

try {
    $maxDaysBeforeImport = 14;
    $externalSystemRepos = $em->getRepository( \Voetbal\External\System::class );
    $leagueRepos = $em->getRepository( \Voetbal\League::class );
    $competitionRepos = $em->getRepository( \Voetbal\Competition::class );
    $externalLeagueRepos = $em->getRepository( \Voetbal\External\League::class );
    $externalTeamRepos = $em->getRepository( \Voetbal\External\Team::class );
    $gameRepos = $em->getRepository( \Voetbal\Game::class );
    $betLineRepos = $em->getRepository( \VOBetting\BetLine::class );
    $layBackRepos = $em->getRepository( \VOBetting\LayBack::class );
    $externalSystemFactory = new ExternalSystemFactory();

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
                $externalTeamRepos,
                $layBackRepos,
                $logger
            );
            $externalSystemHelper->setMaxDaysBeforeImport( $maxDaysBeforeImport );

            foreach ($leagues as $league) {
                $externalLeague = $externalLeagueRepos->findOneBy(array(
                    'externalSystem' => $externalSystemBase,
                    'importableObject' => $league
                ));
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

function getExternalSystem(
    ExternalSystemBase $externalSystemBase,
    CompetitionRepos $competitionRepos,
    ExternalTeamRepos $externalTeamRepos,
    GameRepos $gameRepos,
    BetLineRepos $betLineRepos, LayBackRepos $layBackRepos,
    Logger $logger
) {
    $externalSystem = null;
    if( $externalSystemBase->getName() === "betfair" ) {

        $externalSystem = new \VOBetting\ExternalSystem\Betfair(
            $externalSystemBase, $competitionRepos, $externalTeamRepos, $gameRepos,
            $betLineRepos, $layBackRepos, $logger
        );
    }
//    else if( $externalSystemBase->getName() === "Football Data" ) {
//
//        $externalSystem = new \VOBetting\ExternalSystem\FootballData(
//            $externalSystemBase, $competitionRepos, $externalTeamRepos, $gameRepos,
//            $betLineRepos, $layBackRepos, $logger
//        );
//    }
//    else {
//        throw new \Exception("onbekend extern systeem:" . $externalSystemBase->getName(), E_ERROR );
//    }
    return $externalSystem;
}

function mailAdmin( $errorMessage )
{
    $subject = 'fout bij updateBetLines';
    $body = '
        <p>Hallo,</p>
        <p>            
        Onderstaande fout heeft zich voorgedaan bij de cronjob updateBetLines: ' . $errorMessage . '.
        </p>
        <p>
        met vriendelijke groet,
        <br>
        VOBetting
        </p>';

    $from = "VOBetting";
    $fromEmail = "noreply@VOBetting.nl";
    $headers  = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8" . "\r\n";
    $headers .= "From: ".$from." <" . $fromEmail . ">" . "\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion();
    $params = "-r ".$fromEmail;

    if ( !mail( 'coendunnink@gmail.com', $subject, $body, $headers, $params) ) {
        // $app->flash("error", "We're having trouble with our mail servers at the moment.  Please try again later, or contact us directly by phone.");
        error_log('Mailer Error!' );
        // $app->halt(500);
    }
}
