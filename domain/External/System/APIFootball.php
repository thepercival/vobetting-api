<?php
/**
 * Created by PhpStorm.
 * User: coen
 * Date: 9-4-18
 * Time: 10:41
 */

namespace VOBetting\External\System;

use Voetbal\External\System\Def as SystemDef;
use Voetbal\External\System as ExternalSystem;
use VOBetting\External\System\Importable\BetLine as BetLineImportable;
use VOBetting\External\System\Importer\BetLine as BetLineImporter;
use VOBetting\External\System\APIFootball\BetLine as APIFootballBetLineImporter;
use Voetbal\External\League as ExternalLeague;
use Voetbal\External\System\Importer\TeamGetter;

use VOBetting\BetLine\Repository as BetLineRepos;
use Voetbal\Competition\Repository as CompetitionRepos;
use Voetbal\Game\Repository as GameRepos;
use Voetbal\External\Team\Repository as ExternalTeamRepos;
use VOBetting\LayBack\Repository as LayBackRepos;
use Monolog\Logger;

class APIFootball implements SystemDef, BetLineImportable, TeamGetter
{
    /**
     * @var ExternalSystem
     */
    private $externalSystem;

    public function __construct( ExternalSystem $externalSystem )
    {
        $this->setExternalSystem( $externalSystem );
    }

    public function init() {


    }

    protected function getApiHelper()
    {
        return new APIFootball\ApiHelper( $this->getExternalSystem() );
    }

    public function getBetLineImporter(
        BetLineRepos $repos,
        CompetitionRepos $competitionRepos,
        GameRepos $gameRepos,
        ExternalTeamRepos $externalTeamRepos,
        LayBackRepos $layBackRepos,
        Logger $logger
    ) : BetLineImporter {
        return new APIFootballBetLineImporter(
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
        $apiHelper = $this->getApiHelper();
        $teams = $apiHelper->getData("action=get_standings&league_id=".$externalLeague->getExternalId() );
        if( $teams === null ) {
            return [];
        }
        // var_dump($teams); die();
        return array_map( function( $standing ) {
            return [ "id" => $standing->team_name, "name" => $standing->team_name ];
        }, $teams);
    }
}