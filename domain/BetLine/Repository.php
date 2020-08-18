<?php
/**
 * Created by PhpStorm.
 * User: cdunnink
 * Date: 12-2-2018
 * Time: 10:24
 */

namespace VOBetting\BetLine;

use League\Period\Period;
use VOBetting\BetLine;

/**
 * Class Repository
 * @package VOBetting\LayBack
 */
class Repository extends \Sports\Repository
{
    /**
     * @param Period $gamesPeriod
     * @return array|BetLine[]
     */
    public function findByExt( Period $gamesPeriod): array
    {
        $query = $this->createQueryBuilder('bl')
            ->join("bl.game", "g")
            ->where('g.startDateTime >= :start')
            ->andWhere('g.startDateTime <= :end')
        ;
        $query = $query->setParameter('start', $gamesPeriod->getStartDate());
        $query = $query->setParameter('end', $gamesPeriod->getEndDate());
        return $query->getQuery()->getResult();
    }
}
