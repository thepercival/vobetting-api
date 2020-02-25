<?php
/**
 * Created by PhpStorm.
 * User: coen
 * Date: 22-5-18
 * Time: 12:14
 */

namespace FCToernooi\Sponsor;

use FCToernooi\Sponsor;
use FCToernooi\Tournament;

/**
 * Class Repository
 * @package FCToernooi\Sponsor
 */
class Repository extends \Voetbal\Repository
{
    const MAXNROFSPONSORSPERSCREEN = 9;

    public function find($id, $lockMode = null, $lockVersion = null): ?Sponsor
    {
        return $this->_em->find($this->_entityName, $id, $lockMode, $lockVersion);
    }

    public function checkNrOfSponsors( Tournament $tournament, int $newScreenNr, Sponsor $sponsor = null ) {
        $max = static::MAXNROFSPONSORSPERSCREEN;
        if( $sponsor === null || $sponsor->getScreenNr() !== $newScreenNr ) {
            $max--;
        }
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb = $qb
            ->select('count(s.id)')
            ->from( Sponsor::class, 's')
        ;

        $qb = $qb->where('s.tournament = :tournament')->andWhere('s.screenNr = :screenNr');
        $qb = $qb->setParameter('tournament', $tournament);
        $qb = $qb->setParameter('screenNr', $newScreenNr);

        $nrOfSponsorsPresent = $qb->getQuery()->getSingleScalarResult();
        if( $nrOfSponsorsPresent >= $max ) {
            throw new \Exception("er kan geen sponsor aan schermnummer ".$newScreenNr." meer worden toegevoegd, het maximum van ".static::MAXNROFSPONSORSPERSCREEN." is bereikt", E_ERROR );
        }
    }
}