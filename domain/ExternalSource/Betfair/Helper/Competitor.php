<?php
/**
 * Created by PhpStorm.
 * User: coen
 * Date: 6-3-18
 * Time: 19:55
 */

namespace VOBetting\ExternalSource\Betfair\Helper;

use DateTimeImmutable;
use League\Period\Period;
use stdClass;
use VOBetting\BetLine;
use VOBetting\ExternalSource\Betfair\Helper as BetfairHelper;
use VOBetting\ExternalSource\Betfair\ApiHelper as BetfairApiHelper;
use Voetbal\Competitor as CompetitorBase;
use Psr\Log\LoggerInterface;
use VOBetting\ExternalSource\Betfair;
use Voetbal\Competition;
use Voetbal\ExternalSource\Competitor as ExternalSourceCompetitor;

class Competitor extends BetfairHelper implements ExternalSourceCompetitor
{
    /**
     * @var array|CompetitorBase[]|null
     */
    protected $competitors = [];

    public function __construct(
        Betfair $parent,
        BetfairApiHelper $apiHelper,
        LoggerInterface $logger
    ) {
        parent::__construct(
            $parent,
            $apiHelper,
            $logger
        );
    }

    public function getCompetitors( Competition $competition ): array
    {
        return array_values( $this->getCompetitorsHelper( $competition ) );
    }

    public function getCompetitor( Competition $competition, $id ): ?CompetitorBase
    {
        $competitionCompetitors = $this->getCompetitorsHelper( $competition );
        if( array_key_exists( $id, $competitionCompetitors ) ) {
            return $this->competitors[$id];
        }
        return null;
    }

    protected function getCompetitorsHelper( Competition $competition ): array
    {
        $competitionCompetitors = [];
        $betType = BetLine::_MATCH_ODDS;
        $events = $this->apiHelper->getEvents( $competition->getLeague(), $this->getImportPeriod() );
        foreach( $events as $event  )
        {
            $markets = $this->apiHelper->getMarkets( $event->event->id, $betType );
            foreach ($markets as $market) {
                foreach( $market->runners as $runner ) {
                    if( $runner->metadata->runnerId == $this->parent::THE_DRAW ) {
                        continue;
                    }
                    $competitor = ["id" => $runner->metadata->runnerId, "name" => $runner->runnerName ];
                    if( in_array ( $competitor, $competitionCompetitors ) ) {
                        continue;
                    }
                    $competitors[] = $competitor;
                }
            }
        }
        return $competitionCompetitors;
    }

    /**
     * @param Competition $competition
     * @return array|CompetitorBase[]
     */
//    protected function getCompetitorsHelper( Competition $competition ): array
//    {
//        $competitionCompetitors = [];
//        $association = $competition->getLeague()->getAssociation();
//
//        $apiData = $this->apiHelper->getData(
//            "u-tournament/". $competition->getLeague()->getId() .
//            "/season/". $competition->getId() ."/json",
//            ImportService::COMPETITOR_CACHE_MINUTES
//        );
//
//        $apiDataTeams = $this->convertExternalSourceCompetitors( $apiData );
//
//        /** @var stdClass $externalSourceCompetitor */
//        foreach ($apiDataTeams as $externalSourceCompetitor) {
//
////            if( $externalSourceCompetitor->tournament === null || !property_exists($externalSourceCompetitor->tournament, "uniqueId") ) {
////                continue;
////            }
//            if( array_key_exists( $externalSourceCompetitor->id, $this->competitors ) ) {
//                $competitor = $this->competitors[$externalSourceCompetitor->id];
//                $competitionCompetitors[$competitor->getId()] = $competitor;
//                continue;
//            }
//
//            $newCompetitor = new CompetitorBase( $association, $externalSourceCompetitor->name );
//            $abbreviation = substr( $externalSourceCompetitor->shortName, 0, CompetitorBase::MAX_LENGTH_ABBREVIATION );
//            $newCompetitor->setAbbreviation( $abbreviation );
//            $newCompetitor->setId( $externalSourceCompetitor->id );
//            $this->competitors[$newCompetitor->getId()] = $newCompetitor;
//            $competitionCompetitors[$newCompetitor->getId()] = $newCompetitor;
//        }
//        return $competitionCompetitors;
//    }

    protected function getImportPeriod(): Period {
        $now = new DateTimeImmutable();
        return new Period( $now, $now->modify("+14 days") );
    }

    protected function convertExternalSourceCompetitors( $apiData ) {
        if( property_exists( $apiData, 'teams') ) {
            return $apiData->teams;
        }
        $apiDataTeams = [];

        if( !property_exists( $apiData, 'standingsTables') || count($apiData->standingsTables) === 0 ) {
            return $apiDataTeams;
        }
        $standingsTables = $apiData->standingsTables[0];
        if( !property_exists( $standingsTables, 'tableRows') ) {
            return $apiDataTeams;
        }
        foreach( $standingsTables->tableRows as $tableRow ) {
            if( !property_exists( $tableRow, 'team') ) {
                continue;
            }
            $apiDataTeams[] = $tableRow->team;
        }
        return $apiDataTeams;
    }
}