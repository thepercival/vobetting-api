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

use Voetbal\Competitionseason;
use Voetbal\Competitionseason\Repository as CompetitionseasonRepos;
use Voetbal\External\System\Repository as ExternalSystemRepository;
use Voetbal\Game;
use Voetbal\Competition;
use Voetbal\External\System as ExternalSystemBase;
use Voetbal\External\Team\Repository as ExternalTeamRepos;
use Voetbal\Game\Repository as GameRepos;
use VOBetting\BetLine\Repository as BetLineRepos;
use VOBetting\LayBack\Repository as LayBackRepos;
use VOBetting\BetLine;
use VOBetting\LayBack;


// get batchid from betlines

// walk through external systems
//
$externalSystemRepos = $em->getRepository( \Voetbal\External\System::class );
$competitionRepos = $em->getRepository( \Voetbal\Competition::class );
$competitionseasonRepos = $em->getRepository( \Voetbal\Competitionseason::class );
$externalCompetitionRepos = $em->getRepository( \Voetbal\External\Competition::class );
$externalTeamRepos = $em->getRepository( \Voetbal\External\Team::class );
$gameRepos = $em->getRepository( \Voetbal\Game::class );
$betLineRepos = $em->getRepository( \VOBetting\BetLine::class );
$layBackRepos = $em->getRepository( \VOBetting\LayBack::class );

$externalSystems = $externalSystemRepos->findAll();
$competitions = $competitionRepos->findAll();
$betType = BetLine::_MATCH_ODDS;
foreach( $externalSystems as $externalSystemBase ) {
    echo $externalSystemBase->getName() . PHP_EOL;
    $externalSystem = getExternalSystem(
        $externalSystemBase,
        $competitionseasonRepos,
        $externalTeamRepos,
        $gameRepos,
        $betLineRepos, $layBackRepos
    );

    foreach ($competitions as $competition) {
        if ($competition->getName() !== "Premier League") {
            continue;
        }

        $externalObject = $externalCompetitionRepos->findOneBy(array(
            'externalSystem' => $externalSystemBase,
            'importableObject' => $competition
        ));
        if ($externalObject === null) {
            continue;
        }
        echo "  " . $externalObject->getExternalId() . PHP_EOL;

        try {
            $externalSystem->init();

            $events = $externalSystem->getEvents( $externalObject );

            foreach ($events as $event) {

                $externalSystem->processEvent( $competition, $event, $betType );
            }

            // var_dump($events);
        } catch (\Exception $e) {
            throw new \Exception( $e->getMessage(), E_ERROR );
        }
    }
}

function getExternalSystem(
    ExternalSystemBase $externalSystemBase,
    CompetitionseasonRepos $competitionseasonRepos,
    ExternalTeamRepos $externalTeamRepos,
    GameRepos $gameRepos,
    BetLineRepos $betLineRepos, LayBackRepos $layBackRepos
) {
    $externalSystem = null;
    if( $externalSystemBase->getName() === "betfair" ) {

        $externalSystem = new \VOBetting\ExternalSystem\Betfair(
            $externalSystemBase, $competitionseasonRepos, $externalTeamRepos, $gameRepos,
            $betLineRepos, $layBackRepos
        );
    }

    return $externalSystem;

}
