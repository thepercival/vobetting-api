<?php
/**
 * Created by PhpStorm.
 * User: coen
 * Date: 28-1-17
 * Time: 20:44
 */

namespace VOBetting;

use Doctrine\Common\Collections\ArrayCollection;
use Sports\Game;
use Sports\Place;

class BetLine
{
    /**
     * @var int
     */
    protected $id;
    /**
     * @var Game
     */
    protected $game;
    /**
     * @var int
     */
    protected $betType;
    /**
     * @var Place
     */
    protected $place;
    /**
     * @var ArrayCollection|LayBack[]
     */
    protected $layBacks;

    const _MATCH_ODDS = 1;

    public function __construct(Game $game, $betType)
    {
        $this->setGame($game);
        $this->setBetType($betType);
        $this->layBacks = new ArrayCollection();
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
     * Get betType
     *
     * @return int
     */
    public function getBetType()
    {
        return $this->betType;
    }

    /**
     * @param int $betType
     */
    public function setBetType($betType)
    {
        $this->betType = $betType;
    }

    /**
     * Get game
     *
     * @return Game
     */
    public function getGame()
    {
        return $this->game;
    }

    /**
     * @param Game $game
     */
    public function setGame($game)
    {
        $this->game = $game;
    }

    /**
     * @return Place
     */
    public function getPlace()
    {
        return $this->place;
    }

    /**
     * @param Place $place
     */
    public function setPlace(Place $place = null)
    {
        $this->place = $place;
    }

    /**
     * @return ArrayCollection|LayBack[]
     */
    public function getLayBacks()
    {
        return $this->layBacks;
    }

    /**
     * @param ArrayCollection|LayBack[] $layBacks
     */
    public function setLayBacks(ArrayCollection $layBacks)
    {
        $this->layBacks = $layBacks;
    }
}
