<?php
/**
 * Created by PhpStorm.
 * User: coen
 * Date: 28-1-17
 * Time: 20:44
 */

namespace VOBetting;

use DateTimeImmutable;
use League\Period\Period;

class BetGameFilter
{
    /**
     * @var DateTimeImmutable
     */
    private $start;
    /**
     * @var DateTimeImmutable
     */
    private $end;
    /**
     * @var int|null
     */
    private $competitionId;

    public function __construct()
    {
    }

    public function getPeriod(): Period
    {
        return new Period( $this->getStart(), $this->getEnd() );
    }
    
    public function getStart(): DateTimeImmutable
    {
        return $this->start;
    }

    public function setStart(DateTimeImmutable $start)
    {
        $this->start = $start;
    }

    public function getEnd(): DateTimeImmutable
    {
        return $this->end;
    }

    public function setEnd(DateTimeImmutable $end)
    {
        $this->end = $end;
    }

    public function getCompetitionId(): ?int
    {
        return $this->competitionId;
    }

    /**
     * @param int $competitionId
     */
    public function setCompetitionId($competitionId)
    {
        $this->competitionId = $competitionId;
    }
}
