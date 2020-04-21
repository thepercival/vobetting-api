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
        $betType = BetLine::_MATCH_ODDS;
        $events = $this->apiHelper->getEvents($competition->getLeague());
        foreach ($events as $event) {
            $markets = $this->apiHelper->getMarkets($event->event->id, $betType);
            foreach ($markets as $market) {
                foreach ($market->runners as $runner) {
                    if ($runner->metadata->runnerId == $this->parent::THE_DRAW) {
                        continue;
                    }
                    $competitor = ["id" => $runner->metadata->runnerId, "name" => $runner->runnerName ];
                    if (in_array($competitor, $competitionCompetitors)) {
                        continue;
                    }
                    $competitionCompetitors[] = $competitor;
                }
            }
        }
        return $competitionCompetitors;
    }
}
