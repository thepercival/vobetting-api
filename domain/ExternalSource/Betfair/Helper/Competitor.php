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
use Voetbal\Association;
use Voetbal\Competitor as CompetitorBase;
use Psr\Log\LoggerInterface;
use VOBetting\ExternalSource\Betfair;
use Voetbal\Competition;
use Voetbal\ExternalSource\Competitor as ExternalSourceCompetitor;

class Competitor extends BetfairHelper implements ExternalSourceCompetitor
{
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

    public function getCompetitors(Competition $competition): array
    {
        return array_values($this->getCompetitorsHelper($competition));
    }

    public function getCompetitor(Competition $competition, $id): ?CompetitorBase
    {
        $competitionCompetitors = $this->getCompetitorsHelper($competition);
        if (array_key_exists($id, $competitionCompetitors)) {
            return $competitionCompetitors[$id];
        }
        return null;
    }

    protected function getCompetitorsHelper(Competition $competition): array
    {
        $competitionCompetitors = [];
        $association = $competition->getLeague()->getAssociation();
        $betType = BetLine::_MATCH_ODDS;
        $events = $this->apiHelper->getEvents($competition->getLeague());
        foreach ($events as $event) {
            $markets = $this->apiHelper->getMarkets($event->event->id, $betType);
            foreach ($markets as $market) {
                $competitors = $this->apiHelper->getCompetitors( $association, $market->runners );
                foreach( $competitors as $homeAway => $homeAwayCompetitors ) {
                    foreach( $homeAwayCompetitors as $homeAwayCompetitor ) {
                        $filtered = array_filter( $competitionCompetitors, function( CompetitorBase $competitionCompetitor ) use ($homeAwayCompetitor) : bool  {
                            return $competitionCompetitor->getId() === $homeAwayCompetitor->getId();
                        });
                        if( count( $filtered ) > 0 ) {
                            continue;
                        }
                        $competitionCompetitors[] = $homeAwayCompetitor;
                    }
                }
            }
        }
        return $competitionCompetitors;
    }
}
