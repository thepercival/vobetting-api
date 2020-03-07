<?php

/**
 * Created by PhpStorm.
 * User: coen
 * Date: 14-3-18
 * Time: 12:02
 */

namespace VOBetting\ExternalSource\Betfair;

use Voetbal\ExternalSource;

use Voetbal\External\League as ExternalLeague;
use VOBetting\BetLine;
use League\Period\Period;
use VOBetting\External\System\Betfair as ExternalSystemBetfair;

//use Voetbal\External\System\Importer\Competitor as CompetitorImporter;
//use Voetbal\External\Importable as ImportableObject;
//use Voetbal\Competitor\Service as CompetitorService;
//use Voetbal\Competitor\Repository as CompetitorRepos;
//use Voetbal\External\Object\Service as ExternalObjectService;
//use Voetbal\External\Competitor\Repository as ExternalCompetitorRepos;
//use Voetbal\Association;
//use Voetbal\Competitor as CompetitorBase;
//use Voetbal\External\Competition as ExternalCompetition;

class Competitor
{
    /**
     * @var ExternalSource
     */
    private $externalSource;

    /**
     * @var ApiHelper
     */
    private $apiHelper;

//    /**
//     * @var CompetitorService
//     */
//    private $service;
//
//    /**
//     * @var CompetitorRepos
//     */
//    private $repos;
//
//    /**
//     * @var ExternalObjectService
//     */
//    private $externalObjectService;
//
//    /**
//     * @var ExternalCompetitorRepos
//     */
//    private $externalObjectRepos;

    public function __construct(
        ExternalSource $externalSource,
        ApiHelper $apiHelper/*,
        CompetitorService $service,
        CompetitorRepos $repos,
        ExternalCompetitorRepos $externalRepos*/
    ) {
        $this->externalSource = $externalSource;
        $this->apiHelper = $apiHelper;
        /*$this->service = $service;
        $this->repos = $repos;
        $this->externalObjectRepos = $externalRepos;
        $this->externalObjectService = new ExternalObjectService(
            $this->externalObjectRepos
        );*/
    }

//    public function getCompetitors(ExternalLeague $externalLeague) {
//        $competitors = [];
//        $betType = BetLine::_MATCH_ODDS;
//        $events = $this->apiHelper->getEvents( $externalLeague, $this->getImportPeriod() );
//        foreach( $events as $event  )
//        {
//            $markets = $this->apiHelper->getMarkets( $event->event->id, $betType );
//            foreach ($markets as $market) {
//                foreach( $market->runners as $runner ) {
//                    if( $runner->metadata->runnerId == ExternalSystemBetfair::THE_DRAW ) {
//                        continue;
//                    }
//                    $competitor = ["id" => $runner->metadata->runnerId, "name" => $runner->runnerName ];
//                    if( in_array ( $competitor, $competitors ) ) {
//                        continue;
//                    }
//                    $competitors[] = $competitor;
//                }
//            }
//        }
//        return $competitors;
//    }
//
//    protected function getImportPeriod() {
//        $now = new \DateTimeImmutable();
//        return new Period( $now, $now->modify("+14 days") );
//    }
}
