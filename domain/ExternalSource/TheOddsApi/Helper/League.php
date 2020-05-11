<?php
/**
 * Created by PhpStorm.
 * User: coen
 * Date: 6-3-18
 * Time: 19:55
 */

namespace VOBetting\ExternalSource\TheOddsApi\Helper;

use VOBetting\ExternalSource\TheOddsApi\Helper as TheOddsApiHelper;
use VOBetting\ExternalSource\TheOddsApi\ApiHelper as TheOddsApiApiHelper;
use Voetbal\ExternalSource\League as ExternalSourceLeague;
use Voetbal\League as LeagueBase;
use Voetbal\Association;
use VOBetting\ExternalSource\TheOddsApi;
use Psr\Log\LoggerInterface;
use stdClass;

class League extends TheOddsApiHelper implements ExternalSourceLeague
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
        TheOddsApi $parent,
        TheOddsApiApiHelper $apiHelper,
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
        return $this->apiHelper->getLeagues();
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
            $externalAssociationId = $this->apiHelper->getAssociationId( $externalLeague );
            $association = $this->parent->getAssociation( $externalAssociationId );
            if( $association === null ) {
                continue;
            }

            $name = $this->apiHelper->getLeagueName($externalLeague);
            if ($this->hasName($this->leagues, $name)) {
                continue;
            }
            $league = $this->createLeague($externalLeague, $association) ;
            $this->leagues[$league->getId()] = $league;
        }
    }

    protected function createLeague(stdClass $externalLeague, Association $association): LeagueBase
    {
        $league = new LeagueBase($association, $this->apiHelper->getLeagueName($externalLeague));
        $league->setId($this->apiHelper->getLeagueId($externalLeague));
        return $league;
    }
}
