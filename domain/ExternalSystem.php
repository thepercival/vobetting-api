<?php
/**
 * Created by PhpStorm.
 * User: coen
 * Date: 21-2-18
 * Time: 10:48
 */

namespace VOBetting;

use Voetbal\External\Object as ExternalObject;
use Voetbal\League;

interface ExternalSystem {
    public function init();
    public function getEvents( ExternalObject $externalObject );
    public function processEvent( League $league, $event, $betType );
    public function convertHomeAway( $homeAway );
    public function setMaxDaysBeforeImport( $maxDaysBeforeImport );
}