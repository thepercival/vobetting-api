<?php
/**
 * Created by PhpStorm.
 * User: coen
 * Date: 21-2-18
 * Time: 10:48
 */

namespace VOBetting;

use Voetbal\External\Object as ExternalObject;
use Voetbal\Competition;
use Voetbal\External\System as ExternalSystemBase;

interface ExternalSystem {
    public function init();
    public function getExternalSystem();
    public function setExternalSystem( ExternalSystemBase $externalSystem );
    public function getGame( Competition $competition, \DateTimeImmutable $startDateTime, $runners );
    public function convertHomeAway( $homeAway );
    public function getEvents( ExternalObject $externalObject );
    public function getMarkets( $eventId, $betType );
    public function getBetLines( $marketId, $runnerId );
}