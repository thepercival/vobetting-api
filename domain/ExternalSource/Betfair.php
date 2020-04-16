<?php
/**
 * Created by PhpStorm.
 * User: coen
 * Date: 21-2-18
 * Time: 10:42
 */

namespace VOBetting\ExternalSource;

use Psr\Log\LoggerInterface;
use Voetbal\Competitor as CompetitorBase;
use Voetbal\ExternalSource;
use Voetbal\ExternalSource\Implementation as ExternalSourceImplementation;
use Voetbal\Sport;
use Voetbal\ExternalSource\Sport as ExternalSourceSport;
use VOBetting\ExternalSource\Betfair\Helper\Sport as BetfairHelperSport;
use Voetbal\Association;
use Voetbal\ExternalSource\Association as ExternalSourceAssociation;
use VOBetting\ExternalSource\Betfair\Helper\Association as BetfairHelperAssociation;
use Voetbal\League;
use Voetbal\ExternalSource\League as ExternalSourceLeague;
use VOBetting\ExternalSource\Betfair\Helper\League as BetfairHelperLeague;
use Voetbal\Season;
use Voetbal\ExternalSource\Season as ExternalSourceSeason;
use VOBetting\ExternalSource\Betfair\Helper\Season as BetfairHelperSeason;
use Voetbal\Competition;
use Voetbal\ExternalSource\Competition as ExternalSourceCompetition;
use VOBetting\ExternalSource\Betfair\Helper\Competition as BetfairHelperCompetition;
use Voetbal\Competitor;
use Voetbal\ExternalSource\Competitor as ExternalSourceCompetitor;
use VOBetting\ExternalSource\Betfair\Helper\Competitor as BetfairHelperCompetitor;
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

class Betfair implements ExternalSourceImplementation,
                         ExternalSourceSport, ExternalSourceAssociation, ExternalSourceLeague, ExternalSourceSeason,
                         ExternalSourceCompetition, ExternalSourceCompetitor
{
    public const NAME = "betfair";

    /**
     * @var ExternalSource
     */
    private $externalSource;
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var array
     */
    private $helpers;

    CONST THE_DRAW = 58805;
    public const DEFAULTSPORTID = "AllSports";
    public const DEFAULTSEASONID = "2000/2100";

    public function __construct( ExternalSource $externalSource, LoggerInterface $logger = null )
    {
        $this->logger = $logger;
        $this->helpers = [];
        $this->setExternalSource( $externalSource );
    }

    protected function getApiHelper()
    {
        if (array_key_exists(Betfair\ApiHelper::class, $this->helpers)) {
            return $this->helpers[Betfair\ApiHelper::class];
        }
        $this->helpers[Betfair\ApiHelper::class] = new Betfair\ApiHelper(
            $this->getExternalSource()
        );
        return $this->helpers[Betfair\ApiHelper::class];
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

    /**
     * @return array|Sport[]
     */
    public function getSports(): array
    {
        return $this->getSportHelper()->getSports();
    }

    public function getSport($id = null): ?Sport
    {
        return $this->getSportHelper()->getSport($id);
    }

    protected function getSportHelper(): BetfairHelperSport
    {
        if (array_key_exists(BetfairHelperSport::class, $this->helpers)) {
            return $this->helpers[BetfairHelperSport::class];
        }
        $this->helpers[BetfairHelperSport::class] = new BetfairHelperSport(
            $this,
            $this->getApiHelper(),
            $this->logger
        );
        return $this->helpers[BetfairHelperSport::class];
    }

    /**
     * @return array|Association[]
     */
    public function getAssociations(): array
    {
        return $this->getAssociationHelper()->getAssociations();
    }

    public function getAssociation($id = null): ?Association
    {
        return $this->getAssociationHelper()->getAssociation($id);
    }

    protected function getAssociationHelper(): BetfairHelperAssociation
    {
        if (array_key_exists(BetfairHelperAssociation::class, $this->helpers)) {
            return $this->helpers[BetfairHelperAssociation::class];
        }
        $this->helpers[BetfairHelperAssociation::class] = new BetfairHelperAssociation(
            $this,
            $this->getApiHelper(),
            $this->logger
        );
        return $this->helpers[BetfairHelperAssociation::class];
    }

    /**
     * @return array|League[]
     */
    public function getLeagues(): array
    {
        return $this->getLeagueHelper()->getLeagues();
    }

    public function getLeague($id = null): ?League
    {
        return $this->getLeagueHelper()->getLeague($id);
    }

    protected function getLeagueHelper(): BetfairHelperLeague
    {
        if (array_key_exists(BetfairHelperLeague::class, $this->helpers)) {
            return $this->helpers[BetfairHelperLeague::class];
        }
        $this->helpers[BetfairHelperLeague::class] = new BetfairHelperLeague(
            $this,
            $this->getApiHelper(),
            $this->logger
        );
        return $this->helpers[BetfairHelperLeague::class];
    }

    /**
     * @return array|Season[]
     */
    public function getSeasons(): array
    {
        return $this->getSeasonHelper()->getSeasons();
    }

    public function getSeason($id = null): ?Season
    {
        return $this->getSeasonHelper()->getSeason($id);
    }

    protected function getSeasonHelper(): BetfairHelperSeason
    {
        if (array_key_exists(BetfairHelperSeason::class, $this->helpers)) {
            return $this->helpers[BetfairHelperSeason::class];
        }
        $this->helpers[BetfairHelperSeason::class] = new BetfairHelperSeason(
            $this,
            $this->getApiHelper(),
            $this->logger
        );
        return $this->helpers[BetfairHelperSeason::class];
    }

    /**
     * @return array|Competition[]
     */
    public function getCompetitions(): array
    {
        return $this->getCompetitionHelper()->getCompetitions();
    }

    public function getCompetition($id = null): ?Competition
    {
        return $this->getCompetitionHelper()->getCompetition($id);
    }

    protected function getCompetitionHelper(): BetfairHelperCompetition
    {
        if (array_key_exists(BetfairHelperCompetition::class, $this->helpers)) {
            return $this->helpers[BetfairHelperCompetition::class];
        }
        $this->helpers[BetfairHelperCompetition::class] = new BetfairHelperCompetition(
            $this,
            $this->getApiHelper(),
            $this->logger
        );
        return $this->helpers[BetfairHelperCompetition::class];
    }


    public function getCompetitors( Competition $competition ): array
    {
        return $this->getCompetitorHelper()->getCompetitors($competition);
    }

    public function getCompetitor( Competition $competition, $id ): ?CompetitorBase
    {
        return $this->getCompetitorHelper()->getCompetitor($competition,$id);
    }

    protected function getCompetitorHelper(): BetfairHelperCompetitor
    {
        if (array_key_exists(BetfairHelperCompetitor::class, $this->helpers)) {
            return $this->helpers[BetfairHelperCompetitor::class];
        }
        $this->helpers[BetfairHelperCompetitor::class] = new BetfairHelperCompetitor(
            $this,
            $this->getApiHelper(),
            $this->logger
        );
        return $this->helpers[BetfairHelperCompetitor::class];
    }
}