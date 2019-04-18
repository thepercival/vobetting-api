<?php
/**
 * Created by PhpStorm.
 * User: coen
 * Date: 21-2-18
 * Time: 10:42
 */

namespace VOBetting\External\System;

use Voetbal\External\System as ExternalSystem;
use PeterColes\Betfair\Api\Auth as BetfairAuth;
use VOBetting\BetLine\Repository as BetLineRepos;
use VOBetting\External\System\Importable\BetLine as BetLineImportable;
use VOBetting\External\System\Importer\BetLine as BetLineImporter;
use VOBetting\External\System\Betfair\BetLine as BetfairBetLineImporter;
use VOBetting\External\System\Betfair\Competitor as BetfairCompetitorGetter;
use Voetbal\Competition\Repository as CompetitionRepos;
use Voetbal\Game\Repository as GameRepos;
use Voetbal\External\Competitor\Repository as ExternalCompetitorRepos;
use VOBetting\LayBack\Repository as LayBackRepos;
use VOBetting\Bookmaker\Repository as BookmakerRepos;
use Monolog\Logger;
use Voetbal\External\System\Importer\CompetitorGetter;
use Voetbal\External\League as ExternalLeague;

class Betfair implements \Voetbal\External\System\Def, BetLineImportable, CompetitorGetter
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

        $auth = new BetfairAuth();
        $auth->init(
            $this->externalSystem->getApikey(),
            $this->externalSystem->getUsername(),
            $this->externalSystem->getPassword()
        );
    }

    protected function getApiHelper()
    {
        return new Betfair\ApiHelper( /*$this->getExternalSystem()*/ );
    }

    public function getBetLineImporter(
        BetLineRepos $repos,
        CompetitionRepos $competitionRepos,
        GameRepos $gameRepos,
        ExternalCompetitorRepos $externalCompetitorRepos,
        LayBackRepos $layBackRepos,
        BookmakerRepos $bookmakerRepos,
        Logger $logger
    ) : BetLineImporter {
        return new BetfairBetLineImporter(
            $this->getExternalSystem(),
            $this->getApiHelper(),
            $repos,
            $competitionRepos,
            $gameRepos,
            $externalCompetitorRepos,
            $layBackRepos,
            $bookmakerRepos,
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

    public function getCompetitors( ExternalLeague $externalLeague ): array
    {
        $competitorGetterHelper = new BetfairCompetitorGetter( $this->getExternalSystem(), $this->getApiHelper() );
        return $competitorGetterHelper->getCompetitors( $externalLeague );
    }
}