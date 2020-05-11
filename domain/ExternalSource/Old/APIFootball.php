<?php
/**
 * Created by PhpStorm.
 * User: coen
 * Date: 9-4-18
 * Time: 10:41
 */

namespace VOBetting\ExternalSource\Old;

use Voetbal\ExternalSource\Implementation as ExternalSourceImplementation;
use Voetbal\ExternalSource;
use VOBetting\External\System\Importable\BetLine as BetLineImportable;
use VOBetting\External\System\Importer\BetLine as BetLineImporter;
use VOBetting\External\System\APIFootball\BetLine as APIFootballBetLineImporter;
use Voetbal\External\League as ExternalLeague;
use Voetbal\External\System\Importer\CompetitorGetter;

use VOBetting\BetLine\Repository as BetLineRepos;
use Voetbal\Competition\Repository as CompetitionRepos;
use Voetbal\Game\Repository as GameRepos;
use Voetbal\External\Competitor\Repository as ExternalCompetitorRepos;
use VOBetting\LayBack\Repository as LayBackRepos;
use VOBetting\Bookmaker\Repository as BookmakerRepos;
use Monolog\Logger;

class APIFootball implements ExternalSourceImplementation // SystemDef, BetLineImportable, CompetitorGetter
{
    /**
     * @var ExternalSource
     */
    private $externalSource;

    public function __construct(ExternalSource $externalSource)
    {
        $this->setExternalSource($externalSource);
    }

    public function init()
    {
    }
//
//    protected function getApiHelper()
//    {
//        return new APIFootball\ApiHelper( $this->getExternalSystem() );
//    }
//
//    public function getBetLineImporter(
//        BetLineRepos $repos,
//        CompetitionRepos $competitionRepos,
//        GameRepos $gameRepos,
//        ExternalCompetitorRepos $externalCompetitorRepos,
//        LayBackRepos $layBackRepos,
//        BookmakerRepos $bookmakerRepos,
//        Logger $logger
//    ) : BetLineImporter {
//        return new APIFootballBetLineImporter(
//            $this->getExternalSystem(),
//            $this->getApiHelper(),
//            $repos,
//            $competitionRepos,
//            $gameRepos,
//            $externalCompetitorRepos,
//            $layBackRepos,
//            $bookmakerRepos,
//            $logger
//        );
//    }
//
    /**
     * @return ExternalSource
     */
    public function getExternalSource()
    {
        return $this->externalSource;
    }

    /**
     * @param ExternalSource $externalSource
     */
    public function setExternalSource(ExternalSource $externalSource)
    {
        $this->externalSource = $externalSource;
    }
//
//    public function getCompetitors( ExternalLeague $externalLeague ): array
//    {
//        $apiHelper = $this->getApiHelper();
//        $competitor = $apiHelper->getData("action=get_standings&league_id=".$externalLeague->getExternalId() );
//        if( $competitor === null ) {
//            return [];
//        }
//        // var_dump($competitors); die();
//        return array_map( function( $standing ) {
//            return [ "id" => $standing->team_name, "name" => $standing->team_name ];
//        }, $competitor);
//    }
}
