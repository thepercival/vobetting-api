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

use Voetbal\External\System\Repository as ExternalSystemRepository;
use PeterColes\Betfair\Betfair;

// get batchid from betlines

// walk through external systems
//
$externalSystemRepos = $em->getRepository( \Voetbal\External\System::class );
$competitionRepos = $em->getRepository( \Voetbal\Competition::class );
$externalCompetitionRepos = $em->getRepository( \Voetbal\External\Competition::class );
$externalTeamRepos = $em->getRepository( \Voetbal\External\Team::class );

$externalSystems = $externalSystemRepos->findAll();
$competitions = $competitionRepos->findAll();
foreach( $externalSystems as $externalSystem ) {
    echo $externalSystem->getName() . PHP_EOL;
    foreach( $competitions as $competition ) {
        if( $competition->getName() === "Eredivisie") {
            continue;
        }

        $externalObject = $externalCompetitionRepos->findOneBy( array(
            'externalSystem' => $externalSystem,
            'importableObject' => $competition
        ) );
        if ( $externalObject === null ){
            continue;
        }
        echo "  " . $externalObject->getExternalId() . PHP_EOL;

        $appKey = $externalSystem->getApikey();
        $userName = $externalSystem->getUsername();
        $password = $externalSystem->getPassword();

        try {
            Betfair::init($appKey, $userName, $password);

            $events = Betfair::betting('listEvents', ['filter' => ['competitionIds' => [$externalObject->getExternalId()]]]);

            foreach( $events as $event ) {
                $markets = Betfair::betting('listMarketCatalogue', [
                    'filter' => [
                        'eventIds' => [$event->event->id],
                        'marketBettingTypes' => ["ODDS"],
                    ],
                    'maxResults' => 111,
                    'marketProjection' => ['RUNNER_METADATA']
                ]);

                foreach( $markets as $market ) {
                    if( $market->marketName !== "Match Odds") {
                        continue;
                    }
                    var_dump( "marketId : " . $market->marketId );


                    // get teams by runnerids -> get game by competitionsseason(competition and eventdate) and teams
                    // game for externalsystem exists, continue.....


                    foreach( $market->runners as $runner ) {
                        // use $runner->selectionId as marketbook
                        var_dump($runner->runnerName . " : " . $runner->metadata->runnerId);

                        $marketRunner = Betfair::betting('listRunnerBook', [
                            'marketId' => $market->marketId,
                            'selectionId' => $runner->metadata->runnerId,
                            "priceProjection" => ["priceData" => ["EX_BEST_OFFERS"]],
                            "orderProjection" => "ALL",
                            "matchProjection" => "ROLLED_UP_BY_PRICE"
                        ]);
//                        var_dump($marketRunner);
//                        die();
                    }

                }
                // var_dump($markets);
                die();
            }

            var_dump($events);
        }
        catch( \Exception $e ){
            echo $e->getMessage();
        }
    }
}