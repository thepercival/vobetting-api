<?php
/**
 * Created by PhpStorm.
 * User: coen
 * Date: 6-3-18
 * Time: 19:55
 */

namespace VOBetting\ExternalSource\Matchbook\Helper;

use stdClass;
use VOBetting\ExternalSource\Matchbook;
use VOBetting\ExternalSource\Matchbook\Helper as MatchbookHelper;
use VOBetting\ExternalSource\Matchbook\ApiHelper as MatchbookApiHelper;
use Voetbal\Competition as CompetitionBase;
use Voetbal\League;
use Voetbal\Season;
use Voetbal\ExternalSource;
use Psr\Log\LoggerInterface;
use Voetbal\Sport\Config\Service as SportConfigService;
use Voetbal\Sport;
use Voetbal\ExternalSource\Competition as ExternalSourceCompetition;

class Competition extends MatchbookHelper implements ExternalSourceCompetition
{
    /**
     * @var array|CompetitionBase[]|null
     */
    protected $competitions;
    protected $sportConfigService;

    public function __construct(
        Matchbook $parent,
        MatchbookApiHelper $apiHelper,
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
        return $this->apiHelper->getEventsBySport();
    }

    /**
     *
     *
     * @param array|stdClass[] $externalEvents
     */
    protected function setCompetitions(array $externalEvents)
    {
        $this->competitions = [];
        
        /** @var stdClass $externalEvent */
        foreach ($externalEvents as $externalEvent) {
            $externalSport = $this->apiHelper->getSportData( $externalEvent->{"meta-tags"} );
            if( $externalSport === null ) {
                continue;
            }
            $sport = $this->parent->getSport( $externalSport->{"url-name"} );
            if( $sport === null ) {
                continue;
            }
            $externalLeague = $this->apiHelper->getLeagueData( $externalEvent->{"meta-tags"} );
            if( $externalLeague === null ) {
                continue;
            }
            $league = $this->parent->getLeague( $externalLeague->{"url-name"} );
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
