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
use VOBetting\ExternalSource\Matchbook\Helper\Sport as MatchbookHelperSport;
use Voetbal\Association;
use Voetbal\ExternalSource\Association as ExternalSourceAssociation;
use VOBetting\ExternalSource\Matchbook\Helper\Association as MatchbookHelperAssociation;
use Voetbal\League;
use Voetbal\ExternalSource\League as ExternalSourceLeague;
use VOBetting\ExternalSource\Matchbook\Helper\League as MatchbookHelperLeague;
use Voetbal\Season;
use Voetbal\ExternalSource\Season as ExternalSourceSeason;
use VOBetting\ExternalSource\Matchbook\Helper\Season as MatchbookHelperSeason;
use Voetbal\Competition;
use Voetbal\ExternalSource\Competition as ExternalSourceCompetition;
use VOBetting\ExternalSource\Matchbook\Helper\Competition as MatchbookHelperCompetition;
use Voetbal\Competitor;
use Voetbal\ExternalSource\Competitor as ExternalSourceCompetitor;
use VOBetting\ExternalSource\Matchbook\Helper\Competitor as MatchbookHelperCompetitor;
use VOBetting\Bookmaker;
use VOBetting\ExternalSource\Bookmaker as ExternalSourceBookmaker;
use VOBetting\ExternalSource\Matchbook\Helper\Bookmaker as MatchbookHelperBookmaker;
use VOBetting\LayBack;
use VOBetting\ExternalSource\LayBack as ExternalSourceLayBack;
use VOBetting\ExternalSource\Matchbook\Helper\LayBack as MatchbookHelperLayBack;



class Matchbook implements
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
    public const NAME = "matchbook";
    public const DEFAULTSPORTID = "soocer";
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
        if (array_key_exists(Matchbook\ApiHelper::class, $this->helpers)) {
            return $this->helpers[Matchbook\ApiHelper::class];
        }
        $this->helpers[Matchbook\ApiHelper::class] = new Matchbook\ApiHelper(
            $this->getExternalSource(),
            $this->cacheItemDbRepos
        );
        return $this->helpers[Matchbook\ApiHelper::class];
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

    protected function getSportHelper(): MatchbookHelperSport
    {
        if (array_key_exists(MatchbookHelperSport::class, $this->helpers)) {
            return $this->helpers[MatchbookHelperSport::class];
        }
        $this->helpers[MatchbookHelperSport::class] = new MatchbookHelperSport(
            $this,
            $this->getApiHelper(),
            $this->logger
        );
        return $this->helpers[MatchbookHelperSport::class];
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

    protected function getAssociationHelper(): MatchbookHelperAssociation
    {
        if (array_key_exists(MatchbookHelperAssociation::class, $this->helpers)) {
            return $this->helpers[MatchbookHelperAssociation::class];
        }
        $this->helpers[MatchbookHelperAssociation::class] = new MatchbookHelperAssociation(
            $this,
            $this->getApiHelper(),
            $this->logger
        );
        return $this->helpers[MatchbookHelperAssociation::class];
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

    protected function getLeagueHelper(): MatchbookHelperLeague
    {
        if (array_key_exists(MatchbookHelperLeague::class, $this->helpers)) {
            return $this->helpers[MatchbookHelperLeague::class];
        }
        $this->helpers[MatchbookHelperLeague::class] = new MatchbookHelperLeague(
            $this,
            $this->getApiHelper(),
            $this->logger
        );
        return $this->helpers[MatchbookHelperLeague::class];
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

    protected function getSeasonHelper(): MatchbookHelperSeason
    {
        if (array_key_exists(MatchbookHelperSeason::class, $this->helpers)) {
            return $this->helpers[MatchbookHelperSeason::class];
        }
        $this->helpers[MatchbookHelperSeason::class] = new MatchbookHelperSeason(
            $this,
            $this->getApiHelper(),
            $this->logger
        );
        return $this->helpers[MatchbookHelperSeason::class];
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

    protected function getCompetitionHelper(): MatchbookHelperCompetition
    {
        if (array_key_exists(MatchbookHelperCompetition::class, $this->helpers)) {
            return $this->helpers[MatchbookHelperCompetition::class];
        }
        $this->helpers[MatchbookHelperCompetition::class] = new MatchbookHelperCompetition(
            $this,
            $this->getApiHelper(),
            $this->logger
        );
        return $this->helpers[MatchbookHelperCompetition::class];
    }


    public function getCompetitors(Competition $competition): array
    {
        return $this->getCompetitorHelper()->getCompetitors($competition);
    }

    public function getCompetitor(Competition $competition, $id): ?CompetitorBase
    {
        return $this->getCompetitorHelper()->getCompetitor($competition, $id);
    }

    protected function getCompetitorHelper(): MatchbookHelperCompetitor
    {
        if (array_key_exists(MatchbookHelperCompetitor::class, $this->helpers)) {
            return $this->helpers[MatchbookHelperCompetitor::class];
        }
        $this->helpers[MatchbookHelperCompetitor::class] = new MatchbookHelperCompetitor(
            $this,
            $this->getApiHelper(),
            $this->logger
        );
        return $this->helpers[MatchbookHelperCompetitor::class];
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
    
    protected function getBookmakerHelper(): MatchbookHelperBookmaker
    {
        if (array_key_exists(MatchbookHelperBookmaker::class, $this->helpers)) {
            return $this->helpers[MatchbookHelperBookmaker::class];
        }
        $this->helpers[MatchbookHelperBookmaker::class] = new MatchbookHelperBookmaker(
            $this,
            $this->getApiHelper(),
            $this->logger
        );
        return $this->helpers[MatchbookHelperBookmaker::class];
    }

    public function getLayBacks(Competition $competition): array
    {
        return $this->getLayBackHelper()->getLayBacks($competition);
    }

    public function getLayBack(Competition $competition, $id): ?LayBackBase
    {
        return $this->getLayBackHelper()->getLayBack($competition,$id);
    }

    protected function getLayBackHelper(): MatchbookHelperLayBack
    {
        if (array_key_exists(MatchbookHelperLayBack::class, $this->helpers)) {
            return $this->helpers[MatchbookHelperLayBack::class];
        }
        $this->helpers[MatchbookHelperLayBack::class] = new MatchbookHelperLayBack(
            $this,
            $this->getApiHelper(),
            $this->logger
        );
        return $this->helpers[MatchbookHelperLayBack::class];
    }
}
