<?php
/**
 * Created by PhpStorm.
 * User: coen
 * Date: 14-3-18
 * Time: 19:40
 */

namespace VOBetting\External\System;

use Voetbal\External\System as ExternalSystemBase;

class Factory extends \Voetbal\External\System\Factory
{
    public function create( ExternalSystemBase $externalSystem ) {
        if( $externalSystem->getName() === "betfair" ) {
            return new Betfair($externalSystem);
        }
        return parent::create($externalSystem);
    }
}
