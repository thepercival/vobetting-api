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
use Voetbal\ExternalSource\Sport as ExternalSourceSport;
use Voetbal\Sport as SportBase;
use VOBetting\ExternalSource\Matchbook;
use Psr\Log\LoggerInterface;
use stdClass;

class Sport extends MatchbookHelper implements ExternalSourceSport
{
    /**
     * @var array|SportBase[]|null
     */
    protected $sports;

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
        return $this->apiHelper->getSports();
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
            $name = $externalSport->name;
            if ($this->hasName($this->sports, $name)) {
                continue;
            }
            $sport = $this->createSport($externalSport) ;
            $this->sports[$sport->getId()] = $sport;
        }
    }

    protected function createSport(stdClass $externalSport): SportBase
    {
        $sport = new SportBase($externalSport->name);
        $sport->setId($externalSport->{"url-name"});
        $sport->setTeam(false);
        return $sport;
    }
}
