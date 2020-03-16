<?php
/**
 * Created by PhpStorm.
 * User: coen
 * Date: 14-3-18
 * Time: 19:27
 */

namespace VOBetting\ExternalSource\Betfair;

use PeterColes\Betfair\Betfair as BetfairClient;
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

    public function __construct(
        ExternalSource $externalSource
    ) {
        $this->externalSource = $externalSource;
        $this->client = new BetfairClient(
            $externalSource->getApikey(),
            $externalSource->getUsername(),
            $externalSource->getPassword() );
    }

    public function listCountries( array $params ): array {
        return $this->client->betting(['listCountries']);
    }

//$competitions = $this->client->betting(['listCompetitions']);

//
//    public function getDateFormat() {
//        return 'Y-m-d\TH:i:s\Z';
//    }
//
//    public function convertBetType( $betType )
//    {
//        if( $betType === BetLine::_MATCH_ODDS ) {
//            return 'MATCH_ODDS';
//        }
//        throw new \Exception("unknown bettype", E_ERROR);
//    }

//
//    public function getEvents( ExternalLeague $externalLeague, $importPeriod )
//    {
//        return $this->requestHelper(
//            'listEvents',
//            [
//                'filter' => [
//                    'competitionIds' => [$externalLeague->getExternalId()],
//                    "marketStartTime" => [
//                        "from" => $importPeriod->getStartDate()->format($this->getDateFormat()),
//                        "to" => $importPeriod->getEndDate()->format($this->getDateFormat())
//                    ]
//                ]
//            ]
//        );
//    }
//
//    public function getMarkets( $eventId, $betType )
//    {
//        return $this->requestHelper(
//            'listMarketCatalogue',
//            [
//                'filter' => [
//                    'eventIds' => [$eventId],
//                    'marketTypeCodes' => [$this->convertBetType( $betType )]
//                ],
//                'maxResults' => 3,
//                'marketProjection' => ['RUNNER_METADATA']
//            ]
//        );
//    }
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