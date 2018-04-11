<?php
/**
 * Created by PhpStorm.
 * User: coen
 * Date: 21-2-18
 * Time: 10:42
 */

namespace VOBetting\External\System;

use Voetbal\External\System as ExternalSystem;
use PeterColes\Betfair\Betfair as PeterColesBetfair;
use VOBetting\BetLine\Repository as BetLineRepos;
use VOBetting\External\System\Importable\BetLine as BetLineImportable;
use VOBetting\External\System\Importer\BetLine as BetLineImporter;
use VOBetting\External\System\Betfair\BetLine as BetfairBetLineImporter;
use VOBetting\External\System\Betfair\Team as BetfairTeamGetter;
use Voetbal\Competition\Repository as CompetitionRepos;
use Voetbal\Game\Repository as GameRepos;
use Voetbal\External\Team\Repository as ExternalTeamRepos;
use VOBetting\LayBack\Repository as LayBackRepos;
use Monolog\Logger;
use Voetbal\External\System\Importer\TeamGetter;
use Voetbal\External\League as ExternalLeague;

class Betfair implements \Voetbal\External\System\Def, BetLineImportable, TeamGetter
{
    /**
     * @var ExternalSystem
     */
    private $externalSystem;

    CONST THE_DRAW = 58805;

    public function __construct( ExternalSystem $externalSystem )
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
     * @return ExternalSystem
     */
    public function getExternalSystem()
    {
        return $this->externalSystem;
    }

    /**
     * @param ExternalSystem $externalSystem
     */
    public function setExternalSystem( ExternalSystem $externalSystem )
    {
        $this->externalSystem = $externalSystem;
    }

    public function getTeams( ExternalLeague $externalLeague ): array
    {
        $teamGetterHelper = new BetfairTeamGetter( $this->getExternalSystem(), $this->getApiHelper() );
        return $teamGetterHelper->getTeams( $externalLeague );
    }
}