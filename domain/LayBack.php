<?php
/**
 * Created by PhpStorm.
 * User: coen
 * Date: 28-1-17
 * Time: 20:44
 */

namespace VOBetting;

use Voetbal\External\System as ExternalSystem;

class LayBack
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var boolean
     */
    private $back;

    /**
     * @var float
     */
    private $price;

    /**
     * @var size
     */
    private $size;

    /**
     * @var \DateTimeImmutable
     */
    private $dateTime;
    
    /**
     * @var BetLine
     */
    private $betLine;

    /**
     * @var Bookmaker
     */
    private $bookmaker;

    /**
     * @var System
     */
    private $externalSystem;

    // const _MATCH_ODDS = 1;

    public function __construct(
        \DateTimeImmutable $dateTime,
        BetLine $betLine,
        Bookmaker $bookmaker,
        ExternalSystem $externalSystem
    )
    {
        $this->setDateTime( $dateTime );
        $this->setBetLine( $betLine );
        $this->setBookmaker( $bookmaker );
        $this->setExternalSystem( $externalSystem );
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
     * @param $id
     */
    public function setId( $id )
    {
        $this->id = $id;
    }


    /**
     * Get back
     *
     * @return boolean
     */
    public function getBack()
    {
        return $this->back;
    }

    /**
     * @param $back
     */
    public function setBack( $back )
    {
        $this->back = $back;
    }

    /**
     * Get price
     *
     * @return boolean
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @param $price
     */
    public function setPrice( $price )
    {
        $this->price = $price;
    }

    /**
     * Get size
     *
     * @return boolean
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * @param $size
     */
    public function setSize( $size )
    {
        $this->size = $size;
    }

    /**
     * @return \DateTimeImmutable
     */
    public function getDateTime()
    {
        return $this->dateTime;
    }

    /**
     * @return \DateTimeImmutable
     */
    public function setDateTime( \DateTimeImmutable $dateTime )
    {
        $this->dateTime = $dateTime;
    }
    

    /**
     * Get betLine
     *
     * @return BetLine
     */
    public function getBetLine()
    {
        return $this->betLine;
    }

    /**
     * @param $betLine
     */
    public function setBetLine( $betLine )
    {
        $this->betLine = $betLine;
    }

    /**
     * Get bookmaker
     *
     * @return Bookmaker
     */
    public function getBookmaker()
    {
        return $this->bookmaker;
    }

    /**
     * @param $bookmaker
     */
    public function setBookmaker( $bookmaker )
    {
        $this->bookmaker = $bookmaker;
    }

    /**
     * Get externalSystem
     *
     * @return System
     */
    public function getExternalSystem()
    {
        return $this->externalSystem;
    }

    /**
     * @param $externalSystem
     */
    public function setExternalSystem( ExternalSystem $externalSystem )
    {
        $this->externalSystem = $externalSystem;
    }

    

}