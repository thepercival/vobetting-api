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
use Voetbal\ExternalSource\Sport as ExternalSourceSport;
use Voetbal\Sport as SportBase;
use VOBetting\ExternalSource\TheOddsApi;
use Psr\Log\LoggerInterface;
use stdClass;

class Sport extends TheOddsApiHelper implements ExternalSourceSport
{
    /**
     * @var array|SportBase[]|null
     */
    protected $sports;

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

    public function getSports(): array
    {
        $this->initSports();
        return array_values($this->sports);
    }

    protected function initSports()
    {
        if ($this->sports !== null) {
            return;
        }
        $this->setSports($this->getSportData());
    }

    public function getSport($id = null): ?SportBase
    {
        $this->initSports();
        if (array_key_exists($id, $this->sports)) {
            return $this->sports[$id];
        }
        return null;
    }

    /**
     * @return array|stdClass[]
     */
    protected function getSportData(): array
    {
        return $this->apiHelper->getLeagues();
    }

    /**
     *
     *
     * @param array|stdClass[] $externalLeagues
     */
    protected function setSports(array $externalLeagues)
    {
        $this->sports = [];

        /** @var stdClass $externalLeague */
        foreach ($externalLeagues as $externalLeague) {
            $name = $this->apiHelper->getLeagueName( $externalLeague );
            if ($this->hasName($this->sports, $name)) {
                continue;
            }
            $sport = $this->createSport($externalLeague) ;
            $this->sports[$sport->getId()] = $sport;
        }
    }



    protected function createSport(stdClass $externalLeague): SportBase
    {
        $sport = new SportBase($this->apiHelper->getSportName($externalLeague));
        $sport->setId( $this->apiHelper->getSportId($externalLeague) );
        $sport->setTeam(false);
        return $sport;
    }
}
