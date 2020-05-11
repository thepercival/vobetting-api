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
use Voetbal\CacheItemDb\Repository as CacheItemDbRepository;
use Voetbal\Competitor as CompetitorBase;
use Voetbal\ExternalSource;
use Voetbal\ExternalSource\Implementation as ExternalSourceImplementation;
use Voetbal\Sport;
use Voetbal\ExternalSource\Sport as ExternalSourceSport;
use VOBetting\ExternalSource\TheOddsApi\Helper\Sport as TheOddsApiHelperSport;
use Voetbal\Association;
use Voetbal\ExternalSource\Association as ExternalSourceAssociation;
use VOBetting\ExternalSource\TheOddsApi\Helper\Association as TheOddsApiHelperAssociation;
use Voetbal\League;
use Voetbal\ExternalSource\League as ExternalSourceLeague;
use VOBetting\ExternalSource\TheOddsApi\Helper\League as TheOddsApiHelperLeague;
use Voetbal\Season;
use Voetbal\ExternalSource\Season as ExternalSourceSeason;
use VOBetting\ExternalSource\TheOddsApi\Helper\Season as TheOddsApiHelperSeason;
use Voetbal\Competition;
use Voetbal\ExternalSource\Competition as ExternalSourceCompetition;
use VOBetting\ExternalSource\TheOddsApi\Helper\Competition as TheOddsApiHelperCompetition;
use Voetbal\Competitor;
use Voetbal\ExternalSource\Competitor as ExternalSourceCompetitor;
use VOBetting\ExternalSource\TheOddsApi\Helper\Competitor as TheOddsApiHelperCompetitor;
use VOBetting\Bookmaker;
use VOBetting\ExternalSource\Bookmaker as ExternalSourceBookmaker;
use VOBetting\ExternalSource\TheOddsApi\Helper\Bookmaker as TheOddsApiHelperBookmaker;
use VOBetting\LayBack;
use VOBetting\ExternalSource\LayBack as ExternalSourceLayBack;
use VOBetting\ExternalSource\TheOddsApi\Helper\LayBack as TheOddsApiHelperLayBack;



class TheOddsApi implements
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

    public const NAME = "theoddsapi";
    public const DEFAULTSPORTID = "Soccer";
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
        if (array_key_exists(TheOddsApi\ApiHelper::class, $this->helpers)) {
            return $this->helpers[TheOddsApi\ApiHelper::class];
        }
        $this->helpers[TheOddsApi\ApiHelper::class] = new TheOddsApi\ApiHelper(
            $this->getExternalSource(),
            $this->cacheItemDbRepos
        );
        return $this->helpers[TheOddsApi\ApiHelper::class];
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

    protected function getSportHelper(): TheOddsApiHelperSport
    {
        if (array_key_exists(TheOddsApiHelperSport::class, $this->helpers)) {
            return $this->helpers[TheOddsApiHelperSport::class];
        }
        $this->helpers[TheOddsApiHelperSport::class] = new TheOddsApiHelperSport(
            $this,
            $this->getApiHelper(),
            $this->logger
        );
        return $this->helpers[TheOddsApiHelperSport::class];
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

    protected function getAssociationHelper(): TheOddsApiHelperAssociation
    {
        if (array_key_exists(TheOddsApiHelperAssociation::class, $this->helpers)) {
            return $this->helpers[TheOddsApiHelperAssociation::class];
        }
        $this->helpers[TheOddsApiHelperAssociation::class] = new TheOddsApiHelperAssociation(
            $this,
            $this->getApiHelper(),
            $this->logger
        );
        return $this->helpers[TheOddsApiHelperAssociation::class];
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

    protected function getLeagueHelper(): TheOddsApiHelperLeague
    {
        if (array_key_exists(TheOddsApiHelperLeague::class, $this->helpers)) {
            return $this->helpers[TheOddsApiHelperLeague::class];
        }
        $this->helpers[TheOddsApiHelperLeague::class] = new TheOddsApiHelperLeague(
            $this,
            $this->getApiHelper(),
            $this->logger
        );
        return $this->helpers[TheOddsApiHelperLeague::class];
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

    protected function getSeasonHelper(): TheOddsApiHelperSeason
    {
        if (array_key_exists(TheOddsApiHelperSeason::class, $this->helpers)) {
            return $this->helpers[TheOddsApiHelperSeason::class];
        }
        $this->helpers[TheOddsApiHelperSeason::class] = new TheOddsApiHelperSeason(
            $this,
            $this->getApiHelper(),
            $this->logger
        );
        return $this->helpers[TheOddsApiHelperSeason::class];
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

    protected function getCompetitionHelper(): TheOddsApiHelperCompetition
    {
        if (array_key_exists(TheOddsApiHelperCompetition::class, $this->helpers)) {
            return $this->helpers[TheOddsApiHelperCompetition::class];
        }
        $this->helpers[TheOddsApiHelperCompetition::class] = new TheOddsApiHelperCompetition(
            $this,
            $this->getApiHelper(),
            $this->logger
        );
        return $this->helpers[TheOddsApiHelperCompetition::class];
    }


    public function getCompetitors(Competition $competition): array
    {
        return $this->getCompetitorHelper()->getCompetitors($competition);
    }

    public function getCompetitor(Competition $competition, $id): ?CompetitorBase
    {
        return $this->getCompetitorHelper()->getCompetitor($competition, $id);
    }

    protected function getCompetitorHelper(): TheOddsApiHelperCompetitor
    {
        if (array_key_exists(TheOddsApiHelperCompetitor::class, $this->helpers)) {
            return $this->helpers[TheOddsApiHelperCompetitor::class];
        }
        $this->helpers[TheOddsApiHelperCompetitor::class] = new TheOddsApiHelperCompetitor(
            $this,
            $this->getApiHelper(),
            $this->logger
        );
        return $this->helpers[TheOddsApiHelperCompetitor::class];
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
    
    protected function getBookmakerHelper(): TheOddsApiHelperBookmaker
    {
        if (array_key_exists(TheOddsApiHelperBookmaker::class, $this->helpers)) {
            return $this->helpers[TheOddsApiHelperBookmaker::class];
        }
        $this->helpers[TheOddsApiHelperBookmaker::class] = new TheOddsApiHelperBookmaker(
            $this,
            $this->getApiHelper(),
            $this->logger
        );
        return $this->helpers[TheOddsApiHelperBookmaker::class];
    }

    public function getLayBacks(Competition $competition): array
    {
        return $this->getLayBackHelper()->getLayBacks($competition);
    }

//    public function getLayBack(Competition $competition, $id): ?LayBackBase
//    {
//        return $this->getLayBackHelper()->getLayBack($competition,$id);
//    }

    protected function getLayBackHelper(): TheOddsApiHelperLayBack
    {
        if (array_key_exists(TheOddsApiHelperLayBack::class, $this->helpers)) {
            return $this->helpers[TheOddsApiHelperLayBack::class];
        }
        $this->helpers[TheOddsApiHelperLayBack::class] = new TheOddsApiHelperLayBack(
            $this,
            $this->getApiHelper(),
            $this->logger
        );
        return $this->helpers[TheOddsApiHelperLayBack::class];
    }
}
