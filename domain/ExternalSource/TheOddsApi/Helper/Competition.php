<?php
/**
 * Created by PhpStorm.
 * User: coen
 * Date: 6-3-18
 * Time: 19:55
 */

namespace VOBetting\ExternalSource\TheOddsApi\Helper;

use stdClass;
use VOBetting\ExternalSource\TheOddsApi;
use VOBetting\ExternalSource\TheOddsApi\Helper as TheOddsApiHelper;
use VOBetting\ExternalSource\TheOddsApi\ApiHelper as TheOddsApiApiHelper;
use Voetbal\Competition as CompetitionBase;
use Voetbal\League;
use Voetbal\Season;
use Voetbal\ExternalSource;
use Psr\Log\LoggerInterface;
use Voetbal\Sport\Config\Service as SportConfigService;
use Voetbal\Sport;
use Voetbal\ExternalSource\Competition as ExternalSourceCompetition;

class Competition extends TheOddsApiHelper implements ExternalSourceCompetition
{
    /**
     * @var array|CompetitionBase[]|null
     */
    protected $competitions;
    protected $sportConfigService;

    public function __construct(
        TheOddsApi $parent,
        TheOddsApiApiHelper $apiHelper,
        LoggerInterface $logger
    ) {
        $this->sportConfigService = new SportConfigService();
        parent::__construct(
            $parent,
            $apiHelper,
            $logger
        );
    }

    public function getCompetitions(): array
    {
        $this->initCompetitions();
        return array_values($this->competitions);
    }

    protected function initCompetitions()
    {
        if ($this->competitions !== null) {
            return;
        }
        $this->setCompetitions($this->getCompetitionData());
    }

    public function getCompetition($id = null): ?CompetitionBase
    {
        $this->initCompetitions();
        if (array_key_exists($id, $this->competitions)) {
            return $this->competitions[$id];
        }
        return null;
    }

    /**
     * @return array|stdClass[]
     */
    protected function getCompetitionData(): array
    {
        return $this->apiHelper->getLeagues();
    }

    /**
     *
     *
     * @param array|stdClass[] $externalLeagues
     */
    protected function setCompetitions(array $externalLeagues)
    {
        $this->competitions = [];
        
        /** @var stdClass $externalLeague */
        foreach ($externalLeagues as $externalLeague) {
            $sport = $this->parent->getSport( $this->apiHelper->getSportId($externalLeague) );
            if( $sport->getId() !== $this->parent::DEFAULTSPORTID ) {
                continue;
            }
            $league = $this->parent->getLeague( $this->apiHelper->getLeagueId($externalLeague) );
            if( $league === null ) {
                continue;
            }
            $season = $this->parent->getSeason($this->parent::DEFAULTSEASONID);
            if ($season === null) {
                continue;
            }

            if ($this->hasName($this->competitions, $league->getName())) {
                continue;
            }
            $competition = $this->createCompetition($sport, $league, $season) ;
            if ($competition === null) {
                continue;
            }
            $this->competitions[$competition->getId()] = $competition;
        }
    }

    protected function createCompetition(Sport $sport, League $league, Season $season): ?CompetitionBase
    {
        $newCompetition = new CompetitionBase($league, $season);
        $newCompetition->setStartDateTime($season->getStartDateTime());
        $id = $league->getId() . "-" . $season->getId();
        $newCompetition->setId($id);
        $sportConfig = $this->sportConfigService->createDefault($sport, $newCompetition);
        return $newCompetition;
    }
}
