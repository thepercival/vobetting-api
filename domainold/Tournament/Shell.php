<?php
/**
 * Created by PhpStorm.
 * User: coen
 * Date: 6-10-17
 * Time: 22:50
 */

namespace FCToernooi\Tournament;

use Voetbal\Sport;
use FCToernooi\Tournament;
use FCToernooi\Role;
use FCToernooi\User;

class Shell
{
    /**
     * @var int
     */
    private $tournamentId;

    /**
     * @var int
     */
    private $sportCustomId;

    /**
     * @var string
     */
    private $name;

    /**
     * @var \DateTimeImmutable
     */
    private $startDateTime;

    /**
     * @var bool
     */
    private $hasEditPermissions;

    /**
     * @var bool
     */
    private $public;

    public function __construct( Tournament $tournament, User $user = null )
    {
        $this->tournamentId = $tournament->getId();
        $competition = $tournament->getCompetition();
        $league = $competition->getLeague();
        $this->sportCustomId = $competition->getSportConfigs()->count() > 1 ? 0 : $competition->getSportConfigs(
        )->first()->getSport()->getCustomId();
        $this->name = $league->getName();
        $this->startDateTime = $competition->getStartDateTime();
        $this->hasEditPermissions = ( $user !== null && $tournament->hasRole( $user, Role::ADMIN ) );
        $this->public = $tournament->getPublic();
    }

    /**
     * Get tournamentId
     *
     * @return int
     */
    public function getTournamentId()
    {
        return $this->tournamentId;
    }

    /**
     * @return int
     */
    public function getSportCustomId()
    {
        return $this->sportCustomId;
    }

    /**
     * @param int $sportCustomId
     */
//    protected function setSportCustomId( int $sportCustomId ) {
//        $this->sportCustomId = $sportCustomId;
//    }

    /**
     * @param array $sports
     * @return int
     */
    protected function getSportCustomIdBySports( array $sports ): int {
        if( count($sports) === 1 ) {
            $sport = $sports[0];
            if ($sport->getCustomId() === null) {
                return 0;
            }
            return reset($sports)->getCustomId();
        }
        if( count($sports) > 1 ) {
            return -1;
        }
        return 0;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return \DateTimeImmutable
     */
    public function getStartDateTime()
    {
        return $this->startDateTime;
    }

    /**
     * @return boolean
     */
    public function getHasEditPermissions()
    {
        return $this->hasEditPermissions;
    }

    /**
     * @return boolean
     */
    public function getPublic()
    {
        return $this->public;
    }

}