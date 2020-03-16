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
        $this->client = new BetfairClient();
        $this->client->login();
    }

//
//    public function __construct(
//        ExternalSystem $externalSystem
//    )
//    {
//        $this->externalSystem = $externalSystem;
//        $this->headers['http']['method'] = 'GET';
//        $this->headers['http']['header'] = 'X-Auth-Token: ' . $this->externalSystem->getApikey();
//    }
//
//    protected function getHeaders() {
//        return $this->headers;
//    }
//
//    public function getData( $postUrl ) {
//        $response = file_get_contents($this->externalSystem->getApiurl() . $postUrl, false,
//            stream_context_create( $this->getHeaders()));
//
//        return json_decode($response);
//    }



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
//    private function requestHelper( string $method, array $params )
//    {
//        $betfairBetting = new BetfairBetting();
//        return $betfairBetting->execute([$method,$params]);
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