<?php
/**
 * Created by PhpStorm.
 * User: coen
 * Date: 6-3-18
 * Time: 19:55
 */

namespace VOBetting\ExternalSource\TheOddsApi\Helper;

use DateTime;
use League\Period\Period;
use VOBetting\ExternalSource\TheOddsApi\Helper as TheOddsApiHelper;
use VOBetting\ExternalSource\TheOddsApi\ApiHelper as TheOddsApiApiHelper;
use Voetbal\Association as AssociationBase;
use Voetbal\ExternalSource\Season as ExternalSourceSeason;
use Voetbal\Season as SeasonBase;
use VOBetting\ExternalSource\TheOddsApi;
use Psr\Log\LoggerInterface;
use stdClass;

class Season extends TheOddsApiHelper implements ExternalSourceSeason
{
    /**
     * @var array|SeasonBase[]|null
     */
    protected $seasons;

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

    public function getSeasons(): array
    {
        $this->initSeasons();
        return array_values($this->seasons);
    }

    protected function initSeasons()
    {
        if ($this->seasons !== null) {
            return;
        }
        $this->setSeasons($this->getSeasonData());
    }

    public function getSeason($id = null): ?SeasonBase
    {
        $this->initSeasons();
        if (array_key_exists($id, $this->seasons)) {
            return $this->seasons[$id];
        }
        return null;
    }

    /**
     * @return array|stdClass[]
     */
    protected function getSeasonData(): array
    {
        $class = new stdClass();
        $class->id = $this->parent::DEFAULTSEASONID;
        return [ $class ];
    }

    /**
     *
     *
     * @param array|stdClass[] $externalSeasons
     */
    protected function setSeasons(array $externalSeasons)
    {
        $this->seasons = [];

        /** @var stdClass $externalSeason */
        foreach ($externalSeasons as $externalSeason) {
            $name = $externalSeason->id;
            if ($this->hasName($this->seasons, $name)) {
                continue;
            }
            $season = $this->createSeason($externalSeason) ;
            $this->seasons[$season->getId()] = $season;
        }
    }

    protected function createSeason(stdClass $externalSeason): SeasonBase
    {
        $start = DateTime::createFromFormat('Y-m-d H:i:s', '2000-01-01 00:00:00');
        $end = DateTime::createFromFormat('Y-m-d H:i:s', '2100-01-01 00:00:00');
        $season = new SeasonBase($externalSeason->id, new Period($start, $end));
        $season->setId($externalSeason->id);
        return $season;
    }
}
