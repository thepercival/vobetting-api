<?php
/**
 * Created by PhpStorm.
 * User: coen
 * Date: 21-2-18
 * Time: 10:42
 */

namespace VOBetting\ExternalSource;

use Psr\Log\LoggerInterface;
use VOBetting\LayBack as LayBackBase;
use Sports\CacheItemDb\Repository as CacheItemDbRepository;
use Sports\Competitor as CompetitorBase;
use Sports\ExternalSource;
use Sports\ExternalSource\Implementation as ExternalSourceImplementation;
use Sports\Sport;
use Sports\ExternalSource\Sport as ExternalSourceSport;
use VOBetting\ExternalSource\Betfair\Helper\Sport as BetfairHelperSport;
use Sports\Association;
use Sports\ExternalSource\Association as ExternalSourceAssociation;
use VOBetting\ExternalSource\Betfair\Helper\Association as BetfairHelperAssociation;
use Sports\League;
use Sports\ExternalSource\League as ExternalSourceLeague;
use VOBetting\ExternalSource\Betfair\Helper\League as BetfairHelperLeague;
use Sports\Season;
use Sports\ExternalSource\Season as ExternalSourceSeason;
use VOBetting\ExternalSource\Betfair\Helper\Season as BetfairHelperSeason;
use Sports\Competition;
use Sports\ExternalSource\Competition as ExternalSourceCompetition;
use VOBetting\ExternalSource\Betfair\Helper\Competition as BetfairHelperCompetition;
use Sports\Competitor;
use Sports\ExternalSource\Competitor as ExternalSourceCompetitor;
use VOBetting\ExternalSource\Betfair\Helper\Competitor as BetfairHelperCompetitor;
use VOBetting\Bookmaker;
use VOBetting\ExternalSource\Bookmaker as ExternalSourceBookmaker;
use VOBetting\ExternalSource\Betfair\Helper\Bookmaker as BetfairHelperBookmaker;
use VOBetting\LayBack;
use VOBetting\ExternalSource\LayBack as ExternalSourceLayBack;
use VOBetting\ExternalSource\Betfair\Helper\LayBack as BetfairHelperLayBack;



class Betfair implements
    ExternalSourceImplementation,
    ExternalSourceSport,
    ExternalSourceAssociation,
    ExternalSourceLeague,
    ExternalSourceSeason,
    ExternalSourceCompetition,
    ExternalSourceCompetitor,
    ExternalSourceBookmaker,
    ExternalSourceLayBack
{
    /**
     * @var ExternalSource
     */
    private $externalSource;
    /**
     * @var CacheItemDbRepository
     */
    private $cacheItemDbRepos;
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var array
     */
    private $helpers;

    public const THE_DRAW = 58805;
    public const NAME = "betfair";
    public const DEFAULTSPORTID = "AllSports";
    public const DEFAULTSEASONID = "20002100";

    public function __construct(
        ExternalSource $externalSource,
        CacheItemDbRepository $cacheItemDbRepos,
        LoggerInterface $logger = null
    ) {
        $this->logger = $logger;
        $this->helpers = [];
        $this->setExternalSource($externalSource);
        $this->cacheItemDbRepos = $cacheItemDbRepos;
    }

    protected function getApiHelper()
    {
        if (array_key_exists(Betfair\ApiHelper::class, $this->helpers)) {
            return $this->helpers[Betfair\ApiHelper::class];
        }
        $this->helpers[Betfair\ApiHelper::class] = new Betfair\ApiHelper(
            $this->getExternalSource(),
            $this->cacheItemDbRepos
        );
        return $this->helpers[Betfair\ApiHelper::class];
    }

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


    public function getCompetitors(Competition $competition): array
    {
        return $this->getCompetitorHelper()->getCompetitors($competition);
    }

    public function getCompetitor(Competition $competition, $id): ?CompetitorBase
    {
        return $this->getCompetitorHelper()->getCompetitor($competition, $id);
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

    /**
     * @return array|Bookmaker[]
     */
    public function getBookmakers(): array
    {
        return $this->getBookmakerHelper()->getBookmakers();
    }
    
    public function getBookmaker($id = null): ?Bookmaker
    {
        return $this->getBookmakerHelper()->getBookmaker($id);
    }
    
    protected function getBookmakerHelper(): BetfairHelperBookmaker
    {
        if (array_key_exists(BetfairHelperBookmaker::class, $this->helpers)) {
            return $this->helpers[BetfairHelperBookmaker::class];
        }
        $this->helpers[BetfairHelperBookmaker::class] = new BetfairHelperBookmaker(
            $this,
            $this->getApiHelper(),
            $this->logger
        );
        return $this->helpers[BetfairHelperBookmaker::class];
    }

    public function getLayBacks(Competition $competition): array
    {
        return $this->getLayBackHelper()->getLayBacks($competition);
    }

//    public function getLayBack(Competition $competition, $id): ?LayBackBase
//    {
//        return $this->getLayBackHelper()->getLayBack($competition,$id);
//    }

    protected function getLayBackHelper(): BetfairHelperLayBack
    {
        if (array_key_exists(BetfairHelperLayBack::class, $this->helpers)) {
            return $this->helpers[BetfairHelperLayBack::class];
        }
        $this->helpers[BetfairHelperLayBack::class] = new BetfairHelperLayBack(
            $this,
            $this->getApiHelper(),
            $this->logger
        );
        return $this->helpers[BetfairHelperLayBack::class];
    }
}
