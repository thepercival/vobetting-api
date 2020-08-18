<?php
/**
 * Created by PhpStorm.
 * User: coen
 * Date: 14-3-18
 * Time: 19:27
 */

namespace VOBetting\ExternalSource\TheOddsApi;

use DateTimeImmutable;
use GuzzleHttp\Client;
use League\Period\Period;
use stdClass;
use VOBetting\BetLine;
use VOBetting\Bookmaker;
use VOBetting\ExternalSource\Betfair;
use Sports\Association;
use Sports\CacheItemDb\Repository as CacheItemDbRepository;
use Sports\Competitor;
use Sports\Game;
use Sports\League;
use Sports\ExternalSource;


class ApiHelper
{
    /**
     * @var string|null
     */
    private $sessionKey;
    /**
     * @var Client
     */
    private $client;
    /**
     * @var CacheItemDbRepository
     */
    private $cacheItemDbRepos;
    /**
     * @var ExternalSource
     */
    private $externalSource;

    public function __construct(
        ExternalSource $externalSource,
        CacheItemDbRepository $cacheItemDbRepos
    ) {
        $this->externalSource = $externalSource;
        $this->cacheItemDbRepos = $cacheItemDbRepos;
    }

    protected function getClient()
    {
        if ($this->client === null) {
            $this->client = new Client();
        }
        return $this->client;
    }

    protected function getHeaders()
    {
        return [
            'curl' => [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_HEADER => false,
                CURLOPT_TIMEOUT => 5,
                CURLOPT_CONNECTTIMEOUT => 5
            ],
            'headers' => []
        ];
    }

    protected function getData(string $urlSuffix, int $cacheMinutes = null )
    {
        if( $cacheMinutes !== null ) {
            $data = $this->cacheItemDbRepos->getItem($urlSuffix);
            if ($data !== null) {
                return json_decode($data);
            }
        }

        $apiKeySuffix = "apiKey=" . $this->externalSource->getApikey();
        $apiKeySuffix = ( strpos($urlSuffix, "?") === false ? "?" : "&" ) . $apiKeySuffix;

        $response = $this->getClient()->get(
            $this->externalSource->getApiurl() . $urlSuffix . $apiKeySuffix,
            $this->getHeaders()
        );
//        $headerRequestsRemaining = $response->getHeader("x-requests-remaining");
//        $nrOfRequestRemaining = reset($headerRequestsRemaining );
//        $headerRequestsUsed = $response->getHeader("x-requests-used");
//        $nrOfRequestUsed = reset($headerRequestsUsed );
//        echo "requests-remaining => " . $nrOfRequestRemaining . ", requests-used => " . $nrOfRequestUsed . PHP_EOL;

        if( $cacheMinutes === null ) {
            return json_decode( $response->getBody()->getContents() );
        }

        return json_decode(
            $this->cacheItemDbRepos->saveItem($urlSuffix, $response->getBody()->getContents(), $cacheMinutes)
        );
    }

    /**
     * return array|stdClass
     */
    public function getLeagues(): array
    {
        $urlSuffix = "sports/";
        $retVal = $this->getData( $urlSuffix , 60 * 24 );

        return $retVal->data;
    }

    public function getSportName( stdClass $externalLeague ): string {
        if( strpos( $externalLeague->group, "Soccer - ") !== false ) {
            return "Soccer";
        }
        return $externalLeague->group;
    }

    public function getSportId( stdClass $externalLeague): string {
        return $this->getSportName( $externalLeague );
    }

    public function getAssociationName( stdClass $externalLeague ): string {
        if( strpos( $externalLeague->details, "Soccer") !== false ) {
            return $this->removeIcons( $externalLeague->details );
        }
        return $externalLeague->title;
    }

    public function getAssociationId( stdClass $externalLeague ): string {
        return $this->getAssociationName( $externalLeague );;
    }

    public function getLeagueName( stdClass $externalLeague): string {
        return $externalLeague->title;
    }

    public function getLeagueId( stdClass $externalLeague): string {
        return $externalLeague->key;
    }

    protected function removeIcons(string $title): string {
        $strlen = mb_strlen($title);
        $spacePos = mb_strrpos( $title, " " );
        if( $strlen - $spacePos === 3 ) {
            return trim( mb_substr( $title, 0, $spacePos ) );
        }
        return trim($title);
}

    public function convertCompetitorData( Association $association, string $externalCompetitor ): Competitor {
        $competitor = new Competitor( $association, $externalCompetitor );
        $competitor->setId($externalCompetitor);
        $competitor->setAbbreviation(substr($externalCompetitor, 0, 3));
        return $competitor;
    }

    public function convertBookmakerData( stdClass $externalBookmaker ): Bookmaker {
        $exchange = property_exists( $externalBookmaker->odds, "h2h_lay" );
        $name = $this->getBookmakerName( $externalBookmaker );
        $bookmaker = new Bookmaker( $name, $exchange );
        $bookmaker->setId( $this->getBookmakerId( $externalBookmaker ) );
        return $bookmaker;
    }

    public function getBookmakerName( stdClass $externalBookmaker): string {
        return $externalBookmaker->site_nice;
    }

    public function getBookmakerId( stdClass $externalBookmaker): string {
        return $externalBookmaker->site_key;
    }

    /**
     * @param League $league
     * @return array|stdClass[]
     */
    public function getEventsByLeague(League $league): array
    {
        $urlSuffix = "odds/";

        $urlArgsAsString = $this->convertUrlArgsToString( [
              "sport" => $league->getId(),
              "region" => "eu",
                "mkt" => "h2h"
          ] );
        $retVal = $this->getData( $urlSuffix ."?" . $urlArgsAsString, 60 * 6 ); // 6 HOURS MAX 500 PER MONTH
        return $retVal->data;
    }

    protected function convertUrlArgsToString( array $urlArgs ): string {
        $urlArgsNew = [];
        foreach( $urlArgs as $name => $value ) {
            $urlArgsNew[] = $name . "=" . urlencode( $value );
        }
        return implode( "&", $urlArgsNew );
    }

    /**
     * @param Association $association
     * @param stdClass $externalEvent
     * @return array|Competitor[][]
     * @throws \Exception
     */
    public function getCompetitors( Association $association, stdClass $externalEvent ): array {
        $homeCompetitor = $this->convertCompetitorData( $association, $externalEvent->home_team );
        $competitors = [ Game::HOME => [], Game::AWAY => [] ];

        foreach ($externalEvent->teams as $teamName) {
            $competitor = $this->convertCompetitorData( $association, $teamName );
            $homeAway = null;
            if( $competitor->getId() === $homeCompetitor->getId() ) {
                $homeAway = Game::HOME;
            } else {
                $homeAway = Game::AWAY;
            }
            $competitors[$homeAway][] = $competitor;
        }
        return $competitors;
    }

    /**
     * @param Association $association
     * @param stdClass $externalEvent
     * @return array|Competitor[]
     */
    public function getCompetitorsSimple( Association $association, stdClass $externalEvent ): array {
        $competitors = [];
        foreach ($externalEvent->teams as $teamName) {
            $competitors[] = $this->convertCompetitorData( $association, $teamName );
        }
        return $competitors;
    }
}
