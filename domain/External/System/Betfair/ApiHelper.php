<?php
/**
 * Created by PhpStorm.
 * User: coen
 * Date: 14-3-18
 * Time: 19:27
 */

namespace VOBetting\External\System\Betfair;

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
    public function getDateFormat() {
        return 'Y-m-d\TH:i:s\Z';
    }
}