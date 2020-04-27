<?php

namespace VOBetting;

use Doctrine\ORM\EntityManager;
use League\Period\Period;
use Voetbal\Competition;
use Voetbal\Game;

class BetGameRepository
{
    /**
     * @var EntityManager
     */
    protected $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
        // $this->roundNumberRepos = new RoundNumberRepository($this->em, $this->em->getClassMetaData(RoundNumber::class));
    }

    protected function getSubSelect( bool $homeAway ): string {
        $postFix = $homeAway ? "home" : "away";
        return $this->em->createQueryBuilder()
            ->select("cr".$postFix.".name")
            ->from('Voetbal\Game\Place', "gpp" . $postFix)
            ->join("gpp".$postFix.".place", "pp" . $postFix )
            ->join("pp".$postFix.".competitor", "cr" . $postFix)
            ->where("gpp".$postFix.".game = g")
            ->andWhere("gpp".$postFix.".homeaway = " . ( $homeAway ? 1 : 0 ) )
            ->setMaxResults( 1 )
            ->getDQL();
    }

    public function findByExt( BetGameFilter $betGameFilter ): array {
        $query = $this->em->createQueryBuilder()
            ->select("bl.id as betLineId")
            ->addSelect("g.id as gameId")
            ->addSelect("g.startDateTime as start")
            ->addSelect("c.id as competitionId")
            ->addSelect("l.name as competitionName")
            ->addSelect( "(" . $this->getSubSelect( Game::HOME ) . ") as home")
            ->addSelect( "(" . $this->getSubSelect( Game::AWAY ) . ") as away")
            ->from('VOBetting\BetLine', 'bl')
            ->join("bl.game", "g")
            ->join("g.poule", "p")
            ->join("p.round", "r")
            ->join("r.number", "rn")
            ->join("rn.competition", "c")
            ->join("c.league", "l")
            ->where('g.startDateTime >= :start' )
            ->andWhere('g.startDateTime <= :end' )
            ->orderBy('g.startDateTime', 'ASC')
        ;
        $query = $query->setParameter('start', $betGameFilter->getStart());
        $query = $query->setParameter('end', $betGameFilter->getEnd());
        if( $betGameFilter->getCompetitionId() !== null ) {
            $query = $query->where('c.id = :competitionId' );
            $query = $query->setParameter('competitionId', $betGameFilter->getCompetitionId());
        }
        return $query->getQuery()->getScalarResult();
    }

}