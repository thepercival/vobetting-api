<?php
/**
 * Created by PhpStorm.
 * User: coen
 * Date: 6-3-18
 * Time: 19:55
 */

namespace VOBetting\ExternalSource\TheOddsApi\Helper;

use VOBetting\ExternalSource\TheOddsApi\Helper as TheOddsApiHelper;
use VOBetting\ExternalSource\TheOddsApi\ApiHelper as TheOddsApiApiHelper;
use VOBetting\ExternalSource\Bookmaker as ExternalSourceBookmaker;
use VOBetting\Bookmaker as BookmakerBase;
use VOBetting\ExternalSource\TheOddsApi;
use Sports\Competition;
use Psr\Log\LoggerInterface;
use stdClass;
use Sports\Competitor as CompetitorBase;

class Bookmaker extends TheOddsApiHelper implements ExternalSourceBookmaker
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

    public function getBookmakers(): array
    {
        return array_values($this->getBookmakersHelper());
    }

    public function getBookmaker($id = null): ?BookmakerBase
    {
        $bookmakers = $this->getBookmakersHelper();
        if (array_key_exists($id, $bookmakers)) {
            return $bookmakers[$id];
        }
        return null;
    }

    protected function getBookmakersHelper(): array
    {
        $bookmakers = [];
        foreach( $this->getFilteredCompetitions("soccer_germany_bundesliga") as $competition ) {
            $this->addBookmakers( $competition, $bookmakers );
        }
        return $bookmakers;
    }

    /**
     * @return array|Competition[]
     */
    protected function getFilteredCompetitions( string $leagueKey ): array {
        $competitions = $this->parent->getCompetitions();
        $soccerCompetitions = array_filter( $competitions, function( Competition $competitionIt ): bool  {
            return $competitionIt->getSportBySportId( $this->parent::DEFAULTSPORTID ) !== null;
        });
        if( count( $soccerCompetitions ) === 0 ) {
            return [];
        }
        $keyCompetitions = array_filter( $soccerCompetitions, function( Competition $competitionIt ) use ($leagueKey): bool  {
            return $competitionIt->getLeague()->getId() === $leagueKey;
        });
        if( count( $keyCompetitions ) === 0 ) {
            return array_splice( $soccerCompetitions, 0, 1);
        }
        return array_splice( $keyCompetitions, 0, 1);
    }

    protected function addBookmakers(Competition $competition, array & $bookmakers )
    {
        $externalEvents = $this->apiHelper->getEventsByLeague($competition->getLeague());
        /** @var stdClass $externalEvent */
        foreach ($externalEvents as $externalEvent) {
            /** @var stdClass $externalBookmaker */
            foreach ($externalEvent->sites as $externalBookmaker ) {
                $bookmaker = $this->apiHelper->convertBookmakerData( $externalBookmaker );
                $filtered = array_filter( $bookmakers, function( BookmakerBase $bookmakerIt ) use ($bookmaker) : bool  {
                    return $bookmakerIt->getId() === $bookmaker->getId();
                });
                if( count( $filtered ) > 0 ) {
                    continue;
                }
                $bookmakers[$bookmaker->getId()] = $bookmaker;
            }
        }
    }
}
