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
use Voetbal\External\System as ExternalSystemBase;

interface ExternalSystem {
    public function init();
    public function getExternalSystem();
    public function setExternalSystem( ExternalSystemBase $externalSystem );
    public function getEvents( ExternalObject $externalObject );
    public function processEvent( League $league, $event, $betType );
    public function convertHomeAway( $homeAway );
    public function setMaxDaysBeforeImport( $maxDaysBeforeImport );
}