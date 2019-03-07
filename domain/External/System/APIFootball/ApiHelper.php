<?php
/**
 * Created by PhpStorm.
 * User: coen
 * Date: 14-3-18
 * Time: 19:27
 */

namespace VOBetting\External\System\APIFootball;

use Voetbal\External\System as ExternalSystem;
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
    /**
     * @var ExternalSystem
     */
    private $externalSystem;


    public function __construct(
        ExternalSystem $externalSystem
    )
    {
        $this->externalSystem = $externalSystem;
        // $this->headers['http']['method'] = 'GET';
        // $this->headers['http']['header'] = 'X-Auth-Token: ' . $this->externalSystem->getApikey();
    }
//
//    protected function getHeaders() {
//        return $this->headers;
//    }

    public function getData( $postUrl ) {
        // $postUrl = "action=get_events&from=2018-04-09&to=2018-04-16&league_id=$leagueId"
        $url = $this->externalSystem->getApiurl();
        $apiKey = $this->externalSystem->getApikey();
        // var_dump( $url . "?" . $postUrl . "&APIkey=" . $apiKey ); die();
        $curl_options = array(
            CURLOPT_URL => $url . "?" . $postUrl . "&APIkey=" . $apiKey,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => false,
            CURLOPT_TIMEOUT => 5,
            CURLOPT_CONNECTTIMEOUT => 5
        );
        // // CURLOPT_HTTPHEADER => array ("Content-type:application/json;charset=utf-8"),

        $curl = curl_init();
        curl_setopt_array( $curl, $curl_options );
        $result = curl_exec( $curl );
        return json_decode($result);
    }

    public function getDateFormat() {
        return 'Y-m-d';
    }
}