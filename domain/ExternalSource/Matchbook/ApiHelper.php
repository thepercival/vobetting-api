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
use PeterColes\Matchbook\Matchbook as MatchbookClient;
use stdClass;
use VOBetting\BetLine;
use VOBetting\ExternalSource\Matchbook;
use Voetbal\Association;
use Voetbal\CacheItemDb\Repository as CacheItemDbRepository;
use Voetbal\Competitor;
use Voetbal\Game;
use Voetbal\League;
use Voetbal\ExternalSource;
use Voetbal\Range as VoetbalRange;

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
    public function getSports(): array
    {
        $retVal = $this->getData( "edge/rest/events?sport-ids=15", 60 * 24 );

        // events data gebruiker voor sports, associations(COUNRTY meta-tag), competitions(COMPETITION meta-tag), competitors
        // voor laybacks een andere gebruiken!!
        $sportsData = [];
        foreach( $retVal->events as $event ) {
            foreach( $event->{"meta-tags"} as $metaTag ) {
                if( $metaTag->type !== "SPORT") {
                    continue;
                }
                $sportsData[] = $metaTag;
            }
        }
        return $sportsData;
    }

    /**
     * @return array|stdClass[]
     */
    public function getAssociations(): array
    {

        $retVal = $this->getData( "edge/rest/events?sport-ids=15", 60 * 24 );

        // events data gebruiker voor sports, associations(COUNRTY meta-tag), competitions(COMPETITION meta-tag), competitors
        // voor laybacks een andere gebruiken!!
        $associationData = [];
        foreach( $retVal->events as $event ) {
            foreach( $event->{"meta-tags"} as $metaTag ) {
                if( $metaTag->type !== "COUNTRY") {
                    continue;
                }
                $associationData[] = $metaTag;
            }
        }
        return $associationData;
    }

    public function getDateFormat()
    {
        return 'Y-m-d\TH:i:s.v\Z';
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
    public function getEvents(League $league, Period $period = null): array
    {
        return [];
//        if( $period === null ) {
//            $period = $this->getImportPeriod();
//        }
//        $start = $period->getStartDate()->format($this->getDateFormat());
//        $end = $period->getEndDate()->format($this->getDateFormat());
//        $action = 'listEvents';
//        $cacheId = $this->externalSource->getName() . '-' . $action  . '-' . $league->getId() . '-' . $start . '-' . $end;
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
//                        'competitionIds' => [$league->getId()],
//                        "marketStartTime" => [
//                            "from" => $start,
//                            "to" => $end
//                        ]
//                    ]
//                ]
//            ]
//        );
//        $this->cacheItemDbRepos->saveItem($cacheId, serialize($data), 60 * 24);
//        return $data;
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

    /**
     * @param Association $association
     * @param array|stdClass[] $runners
     * @return array|Competitor[][]
     * @throws \Exception
     */
    public function getCompetitors( Association $association, array $runners ): array {
        return [];
//        $competitors = [ Game::HOME => [], Game::AWAY => [] ];
//        foreach ($runners as $homeAwayBF => $runner) {
//            $id = (int)$runner->metadata->runnerId;
//            if ( $id === Matchbook::THE_DRAW) {
//                continue;
//            }
//            $homeAway = $this->convertHomeAway($runner->sortPriority);
//            $competitor = new Competitor( $association, $runner->runnerName );
//            $competitor->setId($id);
//
//            $competitors[$homeAway][] = $competitor;
//        }
//        return $competitors;
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
