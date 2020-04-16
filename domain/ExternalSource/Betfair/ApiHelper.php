<?php
/**
 * Created by PhpStorm.
 * User: coen
 * Date: 14-3-18
 * Time: 19:27
 */

namespace VOBetting\ExternalSource\Betfair;

use DateTimeImmutable;
use PeterColes\Betfair\Betfair as BetfairClient;
use stdClass;
use VOBetting\BetLine;
use Voetbal\League;
use Voetbal\ExternalSource;

class ApiHelper
{
    /**
     * @var BetfairClient
     */
    private $client;

    /**
     * @var ExternalSource
     */
    private $externalSource;
    /**
     * @var array|stdClass[] |null
     */
    private $listLeagues = null;

    public function __construct(
        ExternalSource $externalSource
    ) {
        $this->externalSource = $externalSource;
        $this->client = new BetfairClient(
            $externalSource->getApikey(),
            $externalSource->getUsername(),
            $externalSource->getPassword()
        );
    }

    public function listLeagues(array $params): array
    {
        if ($this->listLeagues === null) {
            $this->listLeagues = $this->client->betting(['listCompetitions']);
        }
        return $this->listLeagues;
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

    public function getEvents(League $league, $importPeriod)
    {
        return $this->client->betting(
            [
                'listEvents',
                [
                    'filter' => [
                        'competitionIds' => [$league->getId()],
                        "marketStartTime" => [
                            "from" => $importPeriod->getStartDate()->format($this->getDateFormat()),
                            "to" => $importPeriod->getEndDate()->format($this->getDateFormat())
                        ]
                    ]
                ]
            ]
        );
    }

    public function getMarkets($eventId, $betType)
    {
        return $this->client->betting(
            [
                'listMarketCatalogue',
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
    }
//
//    public function getMarketBooks( $marketId ) {
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