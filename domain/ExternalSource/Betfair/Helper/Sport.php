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
use Voetbal\ExternalSource\Sport as ExternalSourceSport;
use Voetbal\Sport as SportBase;
use VOBetting\ExternalSource\Betfair;
use Psr\Log\LoggerInterface;
use stdClass;

class Sport extends BetfairHelper implements ExternalSourceSport
{
    /**
     * @var array|SportBase[]|null
     */
    protected $sports;

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

    public function getSports(): array
    {
        $this->initSports();
        return array_values( $this->sports );
    }

    protected function initSports()
    {
        if( $this->sports !== null ) {
            return;
        }
        $this->setSports( $this->getSportData() );
    }

    public function getSport( $id = null ): ?SportBase
    {
        $this->initSports();
        if( array_key_exists( $id, $this->sports ) ) {
            return $this->sports[$id];
        }
        return null;
    }

    /**
     * @return array|stdClass[]
     */
    protected function getSportData(): array
    {
        $class = new stdClass();
        $class->id = $this->parent::DEFAULTSPORTID;
        return [ $class ];
    }

    /**
     *
     *
     * @param array|stdClass[] $externalSports
     */
    protected function setSports(array $externalSports)
    {
        $this->sports = [];

        /** @var stdClass $externalSport */
        foreach ($externalSports as $externalSport) {

            $name = $externalSport->id;
            if( $this->hasName( $this->sports, $name ) ) {
                continue;
            }
            $sport = $this->createSport( $externalSport ) ;
            $this->sports[$sport->getId()] = $sport;
        }
    }

    protected function createSport( stdClass $externalSport ): SportBase
    {
        $sport = new SportBase( $externalSport->id );
        $sport->setId($externalSport->id);
        $sport->setTeam(false);
        return $sport;
    }
}