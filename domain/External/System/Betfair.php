<?php
/**
 * Created by PhpStorm.
 * User: coen
 * Date: 21-2-18
 * Time: 10:42
 */

namespace VOBetting\External\System;

use VOBetting\External\System as ExternalSystem;
use Voetbal\External\System as ExternalSystemBase;
use PeterColes\Betfair\Betfair as PeterColesBetfair;
use VOBetting\BetLine\Repository as BetLineRepos;
use VOBetting\External\System\Importable\BetLine as BetLineImportable;
use VOBetting\External\System\Importer\BetLine as BetLineImporter;
use VOBetting\External\System\Betfair\BetLine as BetfairBetLineImporter;
use Voetbal\External\System\Importable\Team as TeamImportable;
use Voetbal\External\System\Importer\Team as TeamImporter;
use VOBetting\External\System\Betfair\BetLine as BetfairTeamImporter;
use Voetbal\Competition\Repository as CompetitionRepos;
use Voetbal\Game\Repository as GameRepos;
use Voetbal\External\Team\Repository as ExternalTeamRepos;
use VOBetting\LayBack\Repository as LayBackRepos;
use Monolog\Logger;

class Betfair implements \Voetbal\External\System\Def, BetLineImportable
{
    /**
     * @var ExternalSystem
     */
    private $externalSystem;

    CONST THE_DRAW = 58805;

    public function __construct( ExternalSystemBase $externalSystem )
    {
        $this->setExternalSystem( $externalSystem );
    }

    public function init() {

        PeterColesBetfair::init(
            $this->externalSystem->getApikey(),
            $this->externalSystem->getUsername(),
            $this->externalSystem->getPassword()
        );
    }

    protected function getApiHelper()
    {
        return new Betfair\ApiHelper( $this->getExternalSystem() );
    }

//    public function getTeamImporter(
//        TeamService $service,
//        TeamRepos $repos,
//        ExternalTeamRepos $externalRepos
//    ) : TeamImporter
//    {
//        return new BetfairTeamImporter(
//            $this->getExternalSystem(),
//            $this->getApiHelper(),
//            $service,
//            $repos,
//            $externalRepos
//        );
//    }

    public function getBetLineImporter(
        BetLineRepos $repos,
        CompetitionRepos $competitionRepos,
        GameRepos $gameRepos,
        ExternalTeamRepos $externalTeamRepos,
        LayBackRepos $layBackRepos,
        Logger $logger
    ) : BetLineImporter {
        return new BetfairBetLineImporter(
            $this->getExternalSystem(),
            $this->getApiHelper(),
            $repos,
            $competitionRepos,
            $gameRepos,
            $externalTeamRepos,
            $layBackRepos,
            $logger
        );
    }

    /**
     * @return ExternalSystemBase
     */
    public function getExternalSystem()
    {
        return $this->externalSystem;
    }

    /**
     * @param ExternalSystemBase $externalSystem
     */
    public function setExternalSystem( ExternalSystemBase $externalSystem )
    {
        $this->externalSystem = $externalSystem;
    }
}