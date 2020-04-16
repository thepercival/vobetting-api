<?php
/**
 * Created by PhpStorm.
 * User: coen
 * Date: 6-3-18
 * Time: 19:55
 */

namespace VOBetting\ExternalSource\Betfair\Helper;

use stdClass;
use VOBetting\ExternalSource\Betfair;
use VOBetting\ExternalSource\Betfair\Helper as BetfairHelper;
use VOBetting\ExternalSource\Betfair\ApiHelper as BetfairApiHelper;
use Voetbal\Competition as CompetitionBase;
use Voetbal\ExternalSource;
use Psr\Log\LoggerInterface;
use Voetbal\Sport\Config\Service as SportConfigService;
use Voetbal\Sport;
use Voetbal\ExternalSource\Competition as ExternalSourceCompetition;

class Competition extends BetfairHelper implements ExternalSourceCompetition
{
    /**
     * @var array|CompetitionBase[]|null
     */
    protected $competitions;
    protected $sportConfigService;

    public function __construct(
        Betfair $parent,
        BetfairApiHelper $apiHelper,
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
        return array_values( $this->competitions );
    }

    protected function initCompetitions()
    {
        if( $this->competitions !== null ) {
            return;
        }
        $this->setCompetitions( $this->getCompetitionData() );
    }

    public function getCompetition( $id = null ): ?CompetitionBase
    {
        $this->initCompetitions();
        if( array_key_exists( $id, $this->competitions ) ) {
            return $this->competitions[$id];
        }
        return null;
    }

    /**
     * @return array|stdClass[]
     */
    protected function getCompetitionData(): array
    {
        return $this->apiHelper->listLeagues( [] );
    }

    /**
     *
     *
     * @param array|stdClass[] $externalCompetitions
     */
    protected function setCompetitions(array $externalCompetitions)
    {
        $this->competitions = [];
        $sport = $this->parent->getSport($this->parent::DEFAULTSPORTID );

        /** @var stdClass $externalCompetition */
        foreach ($externalCompetitions as $externalCompetition) {
            if( !property_exists($externalCompetition,"competition") ) {
                continue;
            }
            $name = $externalCompetition->competition->name;
            if( $this->hasName( $this->competitions, $name ) ) {
                continue;
            }
            $competition = $this->createCompetition( $sport, $externalCompetition ) ;
            if( $competition === null ) {
                continue;
            }
            $this->competitions[$competition->getId()] = $competition;
        }
    }

    protected function createCompetition( Sport $sport, stdClass $externalSourceCompetition): ?CompetitionBase
    {
        if( !property_exists($externalSourceCompetition,"competition") ) {
            return null;
        }
        $league = $this->parent->getLeague( $externalSourceCompetition->competition->id );
        if( $league === null ) {
            return null;
        }
        $season = $this->parent->getSeason( $this->parent::DEFAULTSEASONID );
        if( $season === null ) {
            return null;
        }

        $newCompetition = new CompetitionBase( $league, $season );
        $newCompetition->setStartDateTime( $season->getStartDateTime() );
        $id = $league->getId() . "-" . $season->getId();
        $newCompetition->setId( $id );
        $sportConfig = $this->sportConfigService->createDefault( $sport, $newCompetition );
        return $newCompetition;
    }
}