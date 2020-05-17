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
     * @return array|LayBack[]
     */
    public function findByExt( BetLine $betLine, Period $period/*, bool $runner = null, bool $layOrBack = null, bool $exchange = null*/): array
    {
        $query = $this->createQueryBuilder('lb')
            ->join("lb.betLine", "bl")
            ->join("bl.game", "g")
            ->join("lb.bookmaker", "b")
            ->where('lb.betLine = :betLine')
            // ->andWhere('lb.runnerHomeAway = :runner')
            ->andWhere('lb.dateTime >= :start')
            ->andWhere('lb.dateTime <= :end')
            ->addOrderBy('lb.price', 'DESC')
        ;
        $query = $query->setParameter('betLine', $betLine);
        // $query = $query->setParameter('runner', $runner);
        $query = $query->setParameter('start', $period->getStartDate());
        $query = $query->setParameter('end', $period->getEndDate());
        /*if( $layOrBack !== null ) {
            $query = $query->andWhere('lb.back = :layOrBack')->setParameter('layOrBack', $layOrBack);
        }
        if( $exchange !== null ) {
            $query = $query->andWhere('b.exchange = :exchange')->setParameter('exchange', $exchange);
        }*/
        return $query->getQuery()->getResult();
    }
}
