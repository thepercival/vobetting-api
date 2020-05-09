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
use VOBetting\ExternalSource\Betfair;
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

    protected function getData(string $postUrl, int $cacheMinutes = null )
    {
        if( $cacheMinutes !== null ) {
            $data = $this->cacheItemDbRepos->getItem($postUrl);
            if ($data !== null) {
                return json_decode($data);
            }
        }
        $response = $this->getClient()->get(
            $this->externalSource->getApiurl() . $postUrl,
            $this->getHeaders()
        );
        if( $cacheMinutes === null ) {
            return json_decode( $response->getBody()->getContents() );
        }
        return json_decode(
            $this->cacheItemDbRepos->saveItem($postUrl, $response->getBody()->getContents(), $cacheMinutes)
        );
    }

    /**
     * return array|stdClass
     */
    public function getEventsBySport(): array
    {
        $results = [];
        foreach ( $this->getDefaultPeriods() as $period ) {
            $resultsPeriod = $this->getEventsBySportHelper( $period );
            $results = array_merge( $results, $resultsPeriod );
        }
        return $results;
    }

    /**
     * @return array|Period[]
     */
    protected function getDefaultPeriods(): array {
        $today = (new DateTimeImmutable())->setTime(0, 0);
        return [
            new Period($today, $today->modify("+7 days")),
            new Period($today->modify("+14 days"), $today->modify("+21 days"))
        ];
    }

    protected function getEventsBySportHelper( Period $period ): array
    {
        $urlSuffix = "edge/rest/events";
        $urlArgs = array_merge( [ "sport-ids" => 15 ], $this->getUrlPeriodArgs( $period ) );
        $urlArgsAsString = $this->convertUrlArgsToString( $urlArgs );
        $retVal = $this->getData( $urlSuffix ."?" . $urlArgsAsString, 60 * 24 );

        return $retVal->events;
    }

    /**
     * @return array|stdClass[]
     */
    public function getPeriodFilter(): array
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
            return "Match Odds";
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
            $today = (new DateTimeImmutable())->setTime(0, 0);
            $period = new Period($today, $today->modify("+15 days"));
        }
        $urlSuffix = "edge/rest/events";
        $urlArgs = array_merge( [
            "tag-url-names" => $league->getId(),
            "include-event-participants" => "true"
        ], $this->getUrlPeriodArgs( $period ) );
        $urlArgsAsString = $this->convertUrlArgsToString( $urlArgs );
        $retVal = $this->getData( $urlSuffix ."?" . $urlArgsAsString, 60 * 24 );
        return $retVal->events;
    }

    protected function convertUrlArgsToString( array $urlArgs ): string {
        $urlArgsNew = [];
        foreach( $urlArgs as $name => $value ) {
            $urlArgsNew[] = $name . "=" . urlencode( $value );
        }
        return implode( "&", $urlArgsNew );
    }

    protected function getUrlPeriodArgs( Period $period ): array {

        $start = $period->getStartDate();
        $end = $period->getEndDate();
        return ["after" => $start->getTimestamp(), "before" => $end->getTimestamp() ];
    }

    /**
     * @param string|int $eventId
     * @param int $betType
     * @return array|stdClass[]
     * @throws \Exception
     */
    public function getMarkets($eventId, int $betType): array
    {
        // edge/rest/events/1429118577040017/markets
        // &names=Match%20Odds
        // states=open
        // 'https://api.matchbook.com/edge/rest/events/1429118577040017/markets?offset=0&per-page=20&names=Match%20Odds&states=open%

        $urlSuffix = "edge/rest/events/" . $eventId . "/markets";
        $urlArgs = [ "names" => $this->convertBetType( $betType ) ];
        $urlArgsAsString = $this->convertUrlArgsToString( $urlArgs );
        $retVal = $this->getData( $urlSuffix ."?" . $urlArgsAsString );
        return $retVal->markets;
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

    /**
     * @param Association $association
     * @param string $eventName
     * @param array|stdClass[] $runners
     * @return array|Competitor[][]
     * @throws \Exception
     */
    public function getCompetitors( Association $association, string $eventName, array $runners ): array {
        $competitors = [ Game::HOME => [], Game::AWAY => [] ];
        foreach ($runners as $runner) {
            if( property_exists($runner, "event-participant-id" ) === false ) {
                continue;
            }
            $id = (int)$runner->{"event-participant-id"};
            $strPos = strpos( $eventName, $runner->name );
            $homeAway = null;
            if( $strPos === 0 ) {
                $homeAway = Game::HOME;
            } else if( $strPos > 0 ) {
                $homeAway = Game::AWAY;
            } else {
                continue;
            }
            $competitor = new Competitor( $association, $runner->name );
            $competitor->setId($id);

            $competitors[$homeAway][] = $competitor;
        }
        return $competitors;
    }
}
