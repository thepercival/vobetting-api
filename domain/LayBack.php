<?php
/**
 * Created by PhpStorm.
 * User: coen
 * Date: 28-1-17
 * Time: 20:44
 */

namespace VOBetting;

use DateTimeImmutable;

class LayBack
{
    /**
     * @var DateTimeImmutable
     */
    protected $dateTime;
    /**
     * @var BetLine
     */
    protected $betLine;
    /**
     * @var Bookmaker
     */
    protected $bookmaker;

    /**
     * @var int
     */
    protected $id;
    /**
     * @var bool|null
     */
    protected $runnerHomeAway;
    /**
     * @var bool
     */
    protected $back;
    /**
     * @var float
     */
    protected $price;
    /**
     * @var double
     */
    protected $size;

    public const BACK = true;
    public const LAY = false;

    public function __construct(
        DateTimeImmutable $dateTime,
        BetLine $betLine,
        Bookmaker $bookmaker,
        bool $runnerHomeAway = null
    ) {
        $this->setDateTime($dateTime);
        $this->setBetLine($betLine);
        $this->setRunnerHomeAway($runnerHomeAway);
        $this->setBookmaker($bookmaker);
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
    public function setId($id)
    {
        $this->id = $id;
    }

    public function getRunnerHomeAway(): ?bool
    {
        return $this->runnerHomeAway;
    }

    public function setRunnerHomeAway(bool $runnerHomeAway = null)
    {
        $this->runnerHomeAway = $runnerHomeAway;
    }

    public function getBack(): bool
    {
        return $this->back;
    }

    public function setBack(bool $back)
    {
        $this->back = $back;
    }
    
    /**
     * Get price
     *
     * @return double
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @param double $price
     */
    public function setPrice($price)
    {
        $this->price = $price;
    }

    /**
     * Get size
     *
     * @return double
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * @param double $size
     */
    public function setSize($size)
    {
        $this->size = $size;
    }

    /**
     * @return DateTimeImmutable
     */
    public function getDateTime()
    {
        return $this->dateTime;
    }

    public function setDateTime(DateTimeImmutable $dateTime)
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
     * @param BetLine $betLine
     */
    public function setBetLine($betLine)
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
     * @param Bookmaker $bookmaker
     */
    public function setBookmaker($bookmaker)
    {
        $this->bookmaker = $bookmaker;
    }
}
