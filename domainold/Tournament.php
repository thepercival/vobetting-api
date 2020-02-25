<?php
/**
 * Created by PhpStorm.
 * User: coen
 * Date: 6-10-17
 * Time: 22:50
 */

namespace FCToernooi;

use \Doctrine\Common\Collections\ArrayCollection;
use Voetbal\Competition;
use League\Period\Period;

class Tournament
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var Competition
     */
    private $competition;

    /**
     * @var \DateTimeImmutable
     */
    private $breakStartDateTime;

    /**
     * @var int
     */
    private $breakDuration;

    /**
     * @var bool
     */
    private $public;

    /**
     * @var ArrayCollection
     */
    private $roles;

    /**
     * @var ArrayCollection
     */
    private $sponsors;

    /**
     * @var bool
     */
    protected $printed; // @TODO move to exported

    /**
     * @var integer
     */
    protected $exported;

    /**
     * @var bool
     */
    protected $updated; // DEP, false = old struct

    const EXPORTED_PDF = 1;
    const EXPORTED_EXCEL = 2;

    public function __construct( Competition $competition )
    {
        $this->competition = $competition;
        $this->roles = new ArrayCollection();
        $this->sponsors = new ArrayCollection();
        $this->breakDuration = 0;
        $this->updated = true;
    }

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId( $id )
    {
        $this->id = $id;
    }

    /**
     * @return Competition
     */
    public function getCompetition()
    {
        return $this->competition;
    }

    /**
     * @param Competition $competition
     */
    public function setCompetition( Competition $competition )
    {
        $this->competition = $competition;
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getBreakStartDateTime(): ?\DateTimeImmutable
    {
        return $this->breakStartDateTime;
    }

    /**
     * @param \DateTimeImmutable $datetime
     */
    public function setBreakStartDateTime( \DateTimeImmutable $datetime = null )
    {
        $this->breakStartDateTime = $datetime;
    }

    /**
     * @return int
     */
    public function getBreakDuration()
    {
        return $this->breakDuration;
    }

    /**
     * @param int $breakDuration
     */
    public function setBreakDuration( int $breakDuration )
    {
        $this->breakDuration = $breakDuration;
    }


    public function getBreak(): ?Period
    {
        if( $this->getBreakStartDateTime() === null ) {
            return null;
        }
        $endDate = $this->getBreakStartDateTime()->modify("+".$this->getBreakDuration()." minutes");
        return new Period( $this->getBreakStartDateTime(), $endDate );
    }

    /**
     * @param Period|null $period
     */
    public function setBreak( Period $period = null )
    {
        $breakStartDateTime = $period !== null ? $period->getStartDate() : null;
        $durationInSeconds = $period !== null ? $period->getTimestampInterval() : 0;
        $breakDuration = $period !== null ? ((int)($durationInSeconds / 60)) : 0;
        $this->setBreakStartDateTime($breakStartDateTime);
        $this->setBreakDuration($breakDuration);
    }

    /**
     * @return ?bool
     */
    public function getPublic()
    {
        return $this->public;
    }

    /**
     * @param bool $public
     */
    public function setPublic( bool $public )
    {
        $this->public = $public;
    }

    /**
     * @return Role[] | ArrayCollection
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * @param ArrayCollection $roles
     */
    public function setRoles( ArrayCollection $roles)
    {
        $this->roles = $roles;
    }

    public function hasRole( User $user, int $roleValue ) {
        return ( count(array_filter( $this->getRoles()->toArray(), function ( $roleIt, $roleId ) use ( $user, $roleValue ) {
            return ( $roleIt->getUser() === $user && (( $roleIt->getValue() & $roleValue ) === $roleIt->getValue() ) );
        }, ARRAY_FILTER_USE_BOTH)) > 0);
    }

    /**
     * @return Sponsor[] | ArrayCollection
     */
    public function getSponsors()
    {
        return $this->sponsors;
    }

    /**
     * @param ArrayCollection $sponsors
     */
    public function setSponsors( ArrayCollection $sponsors)
    {
        $this->sponsors = $sponsors;
    }

    /**
     * @return integer
     */
    public function getExported()
    {
        return $this->exported;
    }

    /**
     * @param integer $exported
     */
    public function setExported($exported)
    {
        $this->exported = $exported;
    }

    public function getReferee( string $emailaddress )
    {
        $referees = $this->getCompetition()->getReferees();
        foreach( $referees as $referee ) {
            if( $referee->getEmailaddress() === $emailaddress ) {
                return $referee;
            }
        }
        return null;
    }
}