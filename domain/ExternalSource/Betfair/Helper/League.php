<?php
/**
 * Created by PhpStorm.
 * User: coen
 * Date: 6-3-18
 * Time: 19:55
 */

namespace VOBetting\ExternalSource\Betfair\Helper;

use VOBetting\ExternalSource\Betfair\Helper as BetfairHelper;
use VOBetting\ExternalSource\Betfair\ApiHelper as BetfairApiHelper;
use Voetbal\Association as AssociationBase;
use Voetbal\ExternalSource\League as ExternalSourceLeague;
use Voetbal\League as LeagueBase;
use VOBetting\ExternalSource\Betfair;
use Psr\Log\LoggerInterface;
use stdClass;

class League extends BetfairHelper implements ExternalSourceLeague
{
    /**
     * @var array|LeagueBase[]|null
     */
    protected $leagues;
    /**
     * @var LeagueBase
     */
    protected $defaultLeague;

    public function __construct(
        Betfair $parent,
        BetfairApiHelper $apiHelper,
        LoggerInterface $logger
    ) {
        parent::__construct(
            $parent,
            $apiHelper,
            $logger
        );
    }

    public function getLeagues(): array
    {
        $this->initLeagues();
        return array_values( $this->leagues );
    }

    protected function initLeagues()
    {
        if( $this->leagues !== null ) {
            return;
        }
        $this->setLeagues( $this->getLeagueData() );
    }

    public function getLeague( $id = null ): ?LeagueBase
    {
        $this->initLeagues();
        if( array_key_exists( $id, $this->leagues ) ) {
            return $this->leagues[$id];
        }
        return null;
    }

    /**
     * @return array|stdClass[]
     */
    protected function getLeagueData(): array
    {
        return $this->apiHelper->listLeagues( [] );
    }

    /**
     *
     *
     * @param array|stdClass[] $externalLeagues
     */
    protected function setLeagues(array $externalLeagues)
    {
        $this->leagues = [];

        /** @var stdClass $externalLeague */
        foreach ($externalLeagues as $externalLeague) {
            if( !property_exists($externalLeague,"competition") ) {
                continue;
            }
            $name = $externalLeague->competition->name;
            if( $this->hasName( $this->leagues, $name ) ) {
                continue;
            }
            $league = $this->createLeague( $externalLeague ) ;
            $this->leagues[$league->getId()] = $league;
        }
    }

    protected function createLeague( stdClass $externalLeague ): LeagueBase
    {
        $association = $this->parent->getAssociation($externalLeague->competitionRegion);
        $league = new LeagueBase( $association, $externalLeague->competition->name);
        $league->setId($externalLeague->competition->id);
        return $league;
    }
}