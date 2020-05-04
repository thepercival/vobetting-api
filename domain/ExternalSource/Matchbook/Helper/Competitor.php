<?php
/**
 * Created by PhpStorm.
 * User: coen
 * Date: 6-3-18
 * Time: 19:55
 */

namespace VOBetting\ExternalSource\Matchbook\Helper;

use DateTimeImmutable;
use League\Period\Period;
use stdClass;
use VOBetting\BetLine;
use VOBetting\ExternalSource\Matchbook\Helper as MatchbookHelper;
use VOBetting\ExternalSource\Matchbook\ApiHelper as MatchbookApiHelper;
use Voetbal\Association;
use Voetbal\Competitor as CompetitorBase;
use Psr\Log\LoggerInterface;
use VOBetting\ExternalSource\Matchbook;
use Voetbal\Competition;
use Voetbal\ExternalSource\Competitor as ExternalSourceCompetitor;

class Competitor extends MatchbookHelper implements ExternalSourceCompetitor
{
    public function __construct(
        Matchbook $parent,
        MatchbookApiHelper $apiHelper,
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
            /** @var stdClass $externalCompetitor */
            foreach ($externalEvent->{"event-participants"} as $externalCompetitor ) {
                $competitor = $this->apiHelper->convertCompetitorData( $association, $externalCompetitor );
                if( $competitor === null ) {
                    continue;
                }
                if( in_array($competitor, $competitionCompetitors, true ) ) {
                    continue;
                }
                $competitionCompetitors[] = $competitor;
            }
        }
        return $competitionCompetitors;
    }
}
