<?php
/**
 * Created by PhpStorm.
 * User: cdunnink
 * Date: 12-2-2018
 * Time: 10:55
 */

namespace App\Action;

use Slim\ServerRequestInterface;
use JMS\Serializer\Serializer;
use PeterColes\Betfair\Betfair;

final class Testcdk
{
    /**
     * @var Serializer
     */
    protected $serializer;
    /**
     * @var array
     */
    protected $settings;

    public function __construct(Serializer $serializer, $settings)
    {
        $this->serializer = $serializer;
        $this->settings = $settings;
    }

    public function testcdk($request, $response, $args)
    {
        // var_dump($this->settings->exchanges);die();
         $appKey = $this->settings['exchanges']['betfair']['apikey'];
         $userName = $this->settings['exchanges']['betfair']['username'];
         $password = $this->settings['exchanges']['betfair']['password'];

        $sErrorMessage = null;
        try{
            Betfair::init($appKey, $userName, $password);

            $events = Betfair::betting('listEvents', ['filter' => ['textQuery' => 'Serie A']]);


            // $test = array( "cdk" => $userName );
            return $response
                ->withHeader('Content-Type', 'application/json;charset=utf-8')
                ->write($this->serializer->serialize($events, 'json'));
        }
        catch( \Exception $e ){
            $sErrorMessage = $e->getMessage();
        }
        return $response->withStatus(400)->write( $sErrorMessage );
    }
}
