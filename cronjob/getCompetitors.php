<?php
/**
 * Created by PhpStorm.
 * User: coen
 * Date: 11-4-18
 * Time: 11:33
 */

namespace App\Cronjob;

require __DIR__ . '/../vendor/autoload.php';

$settings = require __DIR__ . '/../app/settings.php';
$app = new \Slim\App($settings);

// Set up dependencies
require __DIR__ . '/../app/dependencies.php';

$settings = $app->getContainer()->get('settings');
$em = $app->getContainer()->get('em');

use Monolog\Logger;
use VOBetting\External\System\Factory as ExternalSystemFactory;
use Voetbal\External\System\Importer\TeamGetter;

$logger = new Logger('cronjob-betlines');
$logger->pushProcessor(new \Monolog\Processor\UidProcessor());
$logger->pushHandler(new \Monolog\Handler\StreamHandler($settings['logger']['cronjobpath'] . 'betlines.log', $settings['logger']['level']));

try {
    $maxDaysBeforeImport = 14;
    $externalSystemRepos = $em->getRepository( \Voetbal\External\System::class );
    $leagueRepos = $em->getRepository( \Voetbal\League::class );
    $externalLeagueRepos = $em->getRepository( \Voetbal\External\League::class );
    $externalSystemFactory = new ExternalSystemFactory();

    $externalSystems = $externalSystemRepos->findAll();
    $leagues = $leagueRepos->findAll();
    foreach( $externalSystems as $externalSystemBase ) {
        echo $externalSystemBase->getName() . PHP_EOL;
        if( $externalSystemBase->getName() !== "betfair") { continue;}
        try {
            $externalSystem = $externalSystemFactory->create( $externalSystemBase );
            if( $externalSystem === null or ( $externalSystem instanceof CompetitorGetter ) !== true ) {
                continue;
            }
            $externalSystem->init();

            foreach ($leagues as $league) {

                if( $externalSystemBase->getName() !== "betfair") { continue;}
                $externalLeague = $externalLeagueRepos->findOneBy(array(
                    'externalSystem' => $externalSystemBase,
                    'importableObject' => $league
                ));
                if ($externalLeague === null) {
                    echo $league->getName() . " not found" . PHP_EOL;
                    continue;
                } else {
                    echo $league->getName() . PHP_EOL;
                }
                if( $externalLeague->getImportableObject()->getName() !== "Ligue 2") { continue;}
                $competitors = $externalSystem->getCompetitors( $externalLeague );
                foreach( $competitors as $competitor ) {
                    echo "  " . $competitor["id"] . " : " . $competitor["name"] . PHP_EOL;
                }
            }
        } catch (\Exception $e) {
            echo $e->getMessage() . PHP_EOL;
        }
    }
}
catch( \Exception $e ) {
    echo $e->getMessage();
}