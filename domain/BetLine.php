<?php
/**
 * Created by PhpStorm.
 * User: coen
 * Date: 28-1-17
 * Time: 20:44
 */

namespace VOBetting;

use Voetbal\Game;
use Voetbal\PoulePlace;

class BetLine
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var int
     */
    private $betType;

    /**
     * @var Game
     */
    private $game;

    /**
     * @var PoulePlace
     */
    private $poulePlace;

    const _MATCH_ODDS = 1;

    public function __construct( Game $game, $betType )
    {
        $this->setGame( $game );
        $this->setBetType( $betType );
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
     * Get betType
     *
     * @return int
     */
    public function getBetType()
    {
        return $this->betType;
    }

    /**
     * @param $betType
     */
    public function setBetType( $betType )
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
     * @param $game
     */
    public function setGame( $game )
    {
        $this->game = $game;
    }

    /**
     * Get pouleplace
     *
     * @return PoulePlace
     */
    public function getPoulePlace()
    {
        return $this->poulePlace;
    }

    /**
     * @param $poulePlace
     */
    public function setPoulePlace( PoulePlace $poulePlace )
    {
        $this->poulePlace = $poulePlace;
    }

    

}