<?php
/**
 * Created by PhpStorm.
 * User: coen
 * Date: 14-3-18
 * Time: 19:27
 */

namespace VOBetting\ExternalSource\Matchbook;

use DateTimeImmutable;
use GuzzleHttp\Client;
use League\Period\Period;
use stdClass;
use VOBetting\BetLine;
use Voetbal\Association;
use Voetbal\CacheItemDb\Repository as CacheItemDbRepository;
use Voetbal\Competitor;
use Voetbal\Game;
use Voetbal\League;
use Voetbal\ExternalSource;

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

    protected function getData(string $postUrl, int $cacheMinutes)
    {
        $data = $this->cacheItemDbRepos->getItem($postUrl);
        if ($data !== null) {
            return json_decode($data);
        }

        $response = $this->getClient()->get(
            $this->externalSource->getApiurl() . $postUrl,
            $this->getHeaders()
        );
        return json_decode(
            $this->cacheItemDbRepos->saveItem($postUrl, $response->getBody()->getContents(), $cacheMinutes)
        );
    }

    /**
     * @return array|stdClass[]
     */
    public function getEventsBySport(): array
    {
        $retVal = $this->getData( "edge/rest/events?sport-ids=15", 60 * 24 );
        return $retVal->events;
    }

    public function getDateFormat()
    {
        return 'Y-m-d\TH:i:s.v\Z';
    }

    public function getSportData(array $dataMetaTags ): ?stdClass    {
        return $this->getObjectData($dataMetaTags, "SPORT" );
    }

    public function getAssociationData(array $dataMetaTags ): ?stdClass    {
        return $this->getObjectData($dataMetaTags, "COUNTRY" );
    }

    public function getLeagueData(array $dataMetaTags ): ?stdClass    {
        return $this->getObjectData($dataMetaTags, "COMPETITION" );
    }

    protected function getObjectData(array $dataMetaTags, string $objectType ): ?stdClass    {
        foreach( $dataMetaTags as $metaTag ) {
            if( $metaTag->type === $objectType) {
                return $metaTag;
            }
        }
        return null;
    }

    public function convertCompetitorData( Association $association, stdClass $externalCompetitor ): ?Competitor {
        $competitor = new Competitor( $association, $externalCompetitor->{"participant-name"} );
        $competitor->setId($externalCompetitor->id);
        $competitor->setAbbreviation(substr($competitor->getName(), 0, 3));
        return $competitor;
    }

    public function convertBetType(int $betType): string
    {
        if ($betType === BetLine::_MATCH_ODDS) {
            return 'MATCH_ODDS';
        }
        throw new \Exception("unknown bettype", E_ERROR);
    }

    public function convertBetTypeBack(string $betType): int
    {
        if ($betType === "Match Odds") {
            return BetLine::_MATCH_ODDS;
        }
        throw new \Exception("unknown bettype", E_ERROR);
    }

    /**
     * @param League $league
     * @param Period|null $period
     * @return array|stdClass[]
     */
    public function getEventsByLeague(League $league, Period $period = null): array
    {
        if( $period === null ) {
            $period = $this->getImportPeriod();
        }
        $start = $period->getStartDate()->format($this->getDateFormat());
        $end = $period->getEndDate()->format($this->getDateFormat());

        $retVal = $this->getData( "edge/rest/events?tag-url-names=" . $league->getId() . "&include-event-participants=true", 60 * 24 );
        return $retVal->events;
    }

    /**
     * @param string|int $eventId
     * @param int $betType
     * @return array|stdClass[]
     * @throws \Exception
     */
    public function getMarkets($eventId, int $betType): array
    {
        return [];
//        $action = 'listMarketCatalogue';
//        $cacheId = $this->externalSource->getName() . '-' . $action . '-' . $eventId . '-' . $betType;
//
//        $data = $this->cacheItemDbRepos->getItem($cacheId);
//        if ($data !== null) {
//            return unserialize($data);
//        }
//        $data = $this->client->betting(
//            [
//                $action,
//                [
//                    'filter' => [
//                        'eventIds' => [$eventId],
//                        'marketTypeCodes' => [$this->convertBetType($betType)]
//                    ],
//                    'maxResults' => 3,
//                    'marketProjection' => ['RUNNER_METADATA']
//                ]
//            ]
//        );
//        $this->cacheItemDbRepos->saveItem($cacheId, serialize($data), 60);
//        return $data;
    }

    protected function getImportPeriod(): Period
    {
        $today = (new DateTimeImmutable())->setTime(0, 0);
        return new Period($today, $today->modify("+15 days"));
    }

    public function getMarketBooks( $marketId ) {
        return [];
        // GEEN CACHING!!!
//        return $this->client->betting(
//            [
//                'listMarketBook',
//                [
//                    'marketIds' => [$marketId],
//                    // 'selectionId' => $runnerId,
//                    "priceProjection" => ["priceData" => ["EX_BEST_OFFERS"]],
//                    "orderProjection" => "ALL",
//                    "matchProjection" => "ROLLED_UP_BY_PRICE"
//                ]
//            ]
//        );
    }

    public function convertHomeAway( int $homeAway ): ?bool
    {
        if( $homeAway === 1 ) {
            return Game::HOME;
        }
        else if( $homeAway === 2 ) {
            return Game::AWAY;
        }
        throw new \Exception("matchbook homeaway-value unknown", E_ERROR );
    }
}
