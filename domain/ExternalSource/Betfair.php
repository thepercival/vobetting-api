<?php
/**
 * Created by PhpStorm.
 * User: coen
 * Date: 21-2-18
 * Time: 10:42
 */

namespace VOBetting\ExternalSource;

use Voetbal\ExternalSource;
use Voetbal\ExternalSource\Association as ExternalSourceAssociation;
use Voetbal\ExternalSource\Implementation as ExternalSourceImplementation;
use PeterColes\Betfair\Api\Auth as BetfairAuth;
use VOBetting\BetLine\Repository as BetLineRepos;
use VOBetting\ExternalSource\Importable\BetLine as BetLineImportable;
use VOBetting\ExternalSource\Importer\BetLine as BetLineImporter;
use VOBetting\ExternalSource\Betfair\BetLine as BetfairBetLineImporter;
use VOBetting\ExternalSource\Betfair\Competitor as BetfairCompetitorGetter;
use Voetbal\Competition\Repository as CompetitionRepos;
use Voetbal\Game\Repository as GameRepos;
use Voetbal\External\Competitor\Repository as ExternalCompetitorRepos;
use VOBetting\LayBack\Repository as LayBackRepos;
use VOBetting\Bookmaker\Repository as BookmakerRepos;
use Monolog\Logger;
use Voetbal\External\League as ExternalLeague;

class Betfair implements ExternalSourceImplementation // BetLineImportable, CompetitorGetter
{
    /**
     * @var ExternalSource
     */
    private $externalSource;

    CONST THE_DRAW = 58805;

    public function __construct( ExternalSource $externalSource )
    {
        $this->setExternalSource( $externalSource );
    }

//    public function init() {
//
//        $auth = new BetfairAuth();
//        $auth->init(
//            $this->externalSource->getApikey(),
//            $this->externalSource->getUsername(),
//            $this->externalSource->getPassword()
//        );
//    }
//
//    protected function getApiHelper()
//    {
//        return new Betfair\ApiHelper( /*$this->getExternalSource()*/ );
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
//        return new BetfairBetLineImporter(
//            $this->getExternalSource(),
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
    public function setExternalSource( ExternalSource $externalSource )
    {
        $this->externalSource = $externalSource;
    }



//
//    public function getCompetitors( ExternalLeague $externalLeague ): array
//    {
//        $competitorGetterHelper = new BetfairCompetitorGetter( $this->getExternalSource(), $this->getApiHelper() );
//        return $competitorGetterHelper->getCompetitors( $externalLeague );
//    }
}