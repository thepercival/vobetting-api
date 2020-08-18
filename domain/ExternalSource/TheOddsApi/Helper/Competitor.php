<?php
/**
 * Created by PhpStorm.
 * User: coen
 * Date: 6-3-18
 * Time: 19:55
 */

namespace VOBetting\ExternalSource\TheOddsApi\Helper;

use DateTimeImmutable;
use League\Period\Period;
use stdClass;
use VOBetting\BetLine;
use VOBetting\ExternalSource\TheOddsApi\Helper as TheOddsApiHelper;
use VOBetting\ExternalSource\TheOddsApi\ApiHelper as TheOddsApiApiHelper;
use Sports\Association;
use Sports\Competitor as CompetitorBase;
use Psr\Log\LoggerInterface;
use VOBetting\ExternalSource\TheOddsApi;
use Sports\Competition;
use Sports\ExternalSource\Competitor as ExternalSourceCompetitor;

class Competitor extends TheOddsApiHelper implements ExternalSourceCompetitor
{
    public function __construct(
        TheOddsApi $parent,
        TheOddsApiApiHelper $apiHelper,
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

        $externalEvents = $this->apiHelper->getEventsByLeague($competition->getLeague());
        /** @var stdClass $externalEvent */
        foreach ($externalEvents as $externalEvent) {
            /** @var string $externalCompetitor */
            foreach ($externalEvent->teams as $externalCompetitor ) {
                $competitor = $this->apiHelper->convertCompetitorData( $association, $externalCompetitor );
                $filtered = array_filter( $competitionCompetitors, function( CompetitorBase $competitionCompetitor ) use ($competitor) : bool  {
                    return $competitionCompetitor->getId() === $competitor->getId();
                });
                if( count( $filtered ) > 0 ) {
                    continue;
                }
                $competitionCompetitors[] = $competitor;
            }
        }
        return $competitionCompetitors;
    }
}
