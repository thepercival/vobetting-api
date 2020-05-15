<?php
/**
 * Created by PhpStorm.
 * User: cdunnink
 * Date: 12-2-2018
 * Time: 10:24
 */

namespace VOBetting\LayBack;

use League\Period\Period;
use VOBetting\BetLine;
use VOBetting\LayBack;
use Voetbal\Competitor;
use Voetbal\Game as GameBase;

/**
 * Class Repository
 * @package VOBetting\LayBack
 */
class Repository extends \Voetbal\Repository
{
    /**
     * @param BetLine $betLine
     * @param Period $period
     * @param bool|null $runner
     * @return array|LayBack[]
     */
    public function findByExt( BetLine $betLine, Period $period, bool $runner = null): array
    {
        $query = $this->createQueryBuilder('lb')
            ->join("lb.betLine", "bl")
            ->join("bl.game", "g")
            ->where('lb.dateTime >= :start')
            ->andWhere('lb.dateTime <= :end')
            ->andWhere('lb.runnerHomeAway = :runner')
            ->addOrderBy('lb.size', 'DESC')
        ;
        $query = $query->setParameter('start', $period->getStartDate());
        $query = $query->setParameter('end', $period->getEndDate());
        $query = $query->setParameter('runner', $runner);
        return $query->getQuery()->getResult();
    }
}
