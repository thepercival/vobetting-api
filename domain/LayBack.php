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
     * @var int
     */
    private $id;
    /**
     * @var bool
     */
    private $back;
    /**
     * @var float
     */
    private $price;
    /**
     * @var double
     */
    private $size;

    public function __construct(
        DateTimeImmutable $dateTime,
        BetLine $betLine,
        Bookmaker $bookmaker
    ) {
        $this->setDateTime($dateTime);
        $this->setBetLine($betLine);
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


    /**
     * Get back
     *
     * @return bool
     */
    public function getBack()
    {
        return $this->back;
    }

    /**
     * @param bool $back
     */
    public function setBack($back)
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
