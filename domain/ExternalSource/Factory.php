<?php
/**
 * Created by PhpStorm.
 * User: coen
 * Date: 14-3-18
 * Time: 19:40
 */

namespace VOBetting\ExternalSource;

use Voetbal\ExternalSource;
use Voetbal\ExternalSource\Factory as ExternalSourceFactory;

class Factory extends ExternalSourceFactory
{
    public function create( ExternalSource $externalSource ) {
        if( $externalSource->getName() === "betfair" ) {
            return new Betfair($externalSource);
        }
//        if( $externalSystem->getName() === "API Football" ) {
//            return new APIFootball($externalSystem);
//        }
        return parent::create($externalSource);
    }
}
