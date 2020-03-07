<?php
/**
 * Created by PhpStorm.
 * User: coen
 * Date: 14-3-18
 * Time: 19:27
 */

namespace VOBetting\ExternalSource\Betfair;


use Voetbal\External\League as ExternalLeague;
use PeterColes\Betfair\Api\Betting as BetfairBetting;
use VOBetting\BetLine;
//use Voetbal\External\System as ExternalSystem;
//use Voetbal\External\Object as ExternalObject;
//use Voetbal\Competition\Service as CompetitionService;
//use Voetbal\Competition\Repository as CompetitionRepos;
//use Voetbal\League;
//use Voetbal\Season;
//use Voetbal\Competition;
//use JMS\Serializer\Serializer;
//use Voetbal\External\System\Importable\Competition as CompetitionImportable;
//use Voetbal\External\System\Importer\Competition as CompetitionImporter;
//use Monolog\Logger;

class ApiHelper
{
//    /**
//     * @var array
//     */
//    private $headers;
//
//    /**
//     * @var ExternalSystem
//     */
//    private $externalSystem;
//
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