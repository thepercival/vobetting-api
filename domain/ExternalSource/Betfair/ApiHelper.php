<?php
/**
 * Created by PhpStorm.
 * User: coen
 * Date: 14-3-18
 * Time: 19:27
 */

namespace VOBetting\ExternalSource\Betfair;

use DateTimeImmutable;
use League\Period\Period;
use PeterColes\Betfair\Betfair as BetfairClient;
use stdClass;
use VOBetting\BetLine;
use Voetbal\CacheItemDb\Repository as CacheItemDbRepository;
use Voetbal\League;
use Voetbal\ExternalSource;

class ApiHelper
{
    /**
     * @var BetfairClient
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
        $this->client = new BetfairClient(
            $externalSource->getApikey(),
            $externalSource->getUsername(),
            $externalSource->getPassword()
        );
    }

    /**
     * @param array $params
     * @return array|stdClass[]
     */
    public function listLeagues(array $params): array
    {
        $action = 'listCompetitions';
        $prefix = $this->externalSource->getName() . '-';

        $data = $this->cacheItemDbRepos->getItem($prefix . $action);
        if ($data !== null) {
            return unserialize($data);
        }
        $data = $this->client->betting([$action]);
        $this->cacheItemDbRepos->saveItem($prefix . $action, serialize($data), 60 * 24);
        return $data;
    }

    public function getDateFormat()
    {
        return 'Y-m-d\TH:i:s\Z';
    }

    public function convertBetType($betType)
    {
        if ($betType === BetLine::_MATCH_ODDS) {
            return 'MATCH_ODDS';
        }
        throw new \Exception("unknown bettype", E_ERROR);
    }

    //

    /**
     * @param League $league
     * @param Period $importPeriod
     * @return array|stdClass[]
     */
    public function getEvents(League $league, Period $importPeriod): array
    {
        $start = $importPeriod->getStartDate()->format($this->getDateFormat());
        $end = $importPeriod->getEndDate()->format($this->getDateFormat());
        $action = 'listEvents';
        $cacheId = $this->externalSource->getName() . '-' . $action  . '-' . $league->getId() . '-' . $start . '-' . $end;

        $data = $this->cacheItemDbRepos->getItem($cacheId);
        if ($data !== null) {
            return unserialize($data);
        }
        $data = $this->client->betting(
            [
                $action,
                [
                    'filter' => [
                        'competitionIds' => [$league->getId()],
                        "marketStartTime" => [
                            "from" => $start,
                            "to" => $end
                        ]
                    ]
                ]
            ]
        );
        $this->cacheItemDbRepos->saveItem($cacheId, serialize($data), 60 * 24);
        return $data;
    }

    /**
     * @param string|int $eventId
     * @param int $betType
     * @return array|stdClass[]
     * @throws \Exception
     */
    public function getMarkets($eventId, int $betType): array
    {
        $action = 'listMarketCatalogue';
        $cacheId = $this->externalSource->getName() . '-' . $action . '-' . $eventId . '-' . $betType;

        $data = $this->cacheItemDbRepos->getItem($cacheId);
        if ($data !== null) {
            return unserialize($data);
        }
        $data = $this->client->betting(
            [
                $action,
                [
                    'filter' => [
                        'eventIds' => [$eventId],
                        'marketTypeCodes' => [$this->convertBetType($betType)]
                    ],
                    'maxResults' => 3,
                    'marketProjection' => ['RUNNER_METADATA']
                ]
            ]
        );
        $this->cacheItemDbRepos->saveItem($cacheId, serialize($data), 60);
        return $data;
    }
//
//    public function getMarketBooks( $marketId ) {
//          GEEN CACHING!!!
//        return $this->requestHelper(
//            'listMarketBook',
//            [
//                'marketIds' => [$marketId],
//                // 'selectionId' => $runnerId,
//                "priceProjection" => ["priceData" => ["EX_BEST_OFFERS"]],
//                "orderProjection" => "ALL",
//                "matchProjection" => "ROLLED_UP_BY_PRICE"
//            ]
//        );
//    }
}
