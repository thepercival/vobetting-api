<?php

/**
 * Created by PhpStorm.
 * User: coen
 * Date: 14-3-18
 * Time: 12:02
 */

namespace VOBetting\External\System\Betfair;

use Voetbal\External\System as ExternalSystemBase;

use Voetbal\External\League as ExternalLeague;
use VOBetting\BetLine;
use League\Period\Period;
use VOBetting\External\System\Betfair as ExternalSystemBetfair;

//use Voetbal\External\System\Importer\Team as TeamImporter;
//use Voetbal\External\Importable as ImportableObject;
//use Voetbal\Team\Service as TeamService;
//use Voetbal\Team\Repository as TeamRepos;
//use Voetbal\External\Object\Service as ExternalObjectService;
//use Voetbal\External\Team\Repository as ExternalTeamRepos;
//use Voetbal\Association;
//use Voetbal\Team as TeamBase;
//use Voetbal\External\Competition as ExternalCompetition;

class Team
{
    /**
     * @var ExternalSystemBase
     */
    private $externalSystemBase;

    /**
     * @var ApiHelper
     */
    private $apiHelper;

//    /**
//     * @var TeamService
//     */
//    private $service;
//
//    /**
//     * @var TeamRepos
//     */
//    private $repos;
//
//    /**
//     * @var ExternalObjectService
//     */
//    private $externalObjectService;
//
//    /**
//     * @var ExternalTeamRepos
//     */
//    private $externalObjectRepos;

    public function __construct(
        ExternalSystemBase $externalSystemBase,
        ApiHelper $apiHelper/*,
        TeamService $service,
        TeamRepos $repos,
        ExternalTeamRepos $externalRepos*/
    ) {
        $this->externalSystemBase = $externalSystemBase;
        $this->apiHelper = $apiHelper;
        /*$this->service = $service;
        $this->repos = $repos;
        $this->externalObjectRepos = $externalRepos;
        $this->externalObjectService = new ExternalObjectService(
            $this->externalObjectRepos
        );*/
    }

    public function getTeams(ExternalLeague $externalLeague) {
        $teams = [];
        $betType = BetLine::_MATCH_ODDS;
        $events = $this->apiHelper->getEvents( $externalLeague, $this->getImportPeriod() );
        foreach( $events as $event  )
        {
            $markets = $this->apiHelper->getMarkets( $event->event->id, $betType );
            foreach ($markets as $market) {
                foreach( $market->runners as $runner ) {
                    if( $runner->metadata->runnerId == ExternalSystemBetfair::THE_DRAW ) {
                        continue;
                    }
                    $team = ["id" => $runner->metadata->runnerId, "name" => $runner->runnerName ];
                    if( in_array ( $team , $teams ) ) {
                        continue;
                    }
                    $teams[] = $team;
                }
            }
        }
        return $teams;
    }

    protected function getImportPeriod() {
        $now = new \DateTimeImmutable();
        return new Period( $now, $now->modify("+14 days") );
    }
}
