<?php
/**
 * Created by PhpStorm.
 * User: coen
 * Date: 6-3-18
 * Time: 19:55
 */

namespace VOBetting\ExternalSource\Matchbook\Helper;

use VOBetting\ExternalSource\Matchbook\Helper as MatchbookHelper;
use VOBetting\ExternalSource\Matchbook\ApiHelper as MatchbookApiHelper;
use Voetbal\ExternalSource\League as ExternalSourceLeague;
use Voetbal\League as LeagueBase;
use Voetbal\Association;
use VOBetting\ExternalSource\Matchbook;
use Psr\Log\LoggerInterface;
use stdClass;

class League extends MatchbookHelper implements ExternalSourceLeague
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
        Matchbook $parent,
        MatchbookApiHelper $apiHelper,
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
        return array_values($this->leagues);
    }

    protected function initLeagues()
    {
        if ($this->leagues !== null) {
            return;
        }
        $this->setLeagues($this->getLeagueData());
    }

    public function getLeague($id = null): ?LeagueBase
    {
        $this->initLeagues();
        if (array_key_exists($id, $this->leagues)) {
            return $this->leagues[$id];
        }
        return null;
    }

    /**
     * @return array|stdClass[]
     */
    protected function getLeagueData(): array
    {
        return $this->apiHelper->getEventsBySport();
    }

    /**
     *
     *
     * @param array|stdClass[] $externalEvents
     */
    protected function setLeagues(array $externalEvents)
    {
        $this->leagues = [];

        /** @var stdClass $externalEvent */
        foreach ($externalEvents as $externalEvent) {
            $externalAssociation = $this->apiHelper->getAssociationData( $externalEvent->{"meta-tags"} );
            if( $externalAssociation === null ) {
                continue;
            }
            $association = $this->parent->getAssociation( $externalAssociation->{"url-name"} );
            if( $association === null ) {
                continue;
            }

            $externalLeague = $this->apiHelper->getLeagueData( $externalEvent->{"meta-tags"} );
            if( $externalLeague === null ) {
                continue;
            }

            $name = $externalLeague->name;
            if ($this->hasName($this->leagues, $name)) {
                continue;
            }
            $league = $this->createLeague($externalLeague, $association) ;
            $this->leagues[$league->getId()] = $league;
        }
    }

    protected function createLeague(stdClass $externalLeague, Association $association): LeagueBase
    {
        $league = new LeagueBase($association, $externalLeague->name);
        $league->setId($externalLeague->{"url-name"});
        return $league;
    }
}
