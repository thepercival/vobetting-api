<?php
/**
 * Created by PhpStorm.
 * User: coen
 * Date: 28-1-17
 * Time: 20:44
 */

namespace VOBetting;

use DateTimeImmutable;

class BetGame
{
    /**
     * @var string | int
     */
    public $gameId;
    /**
     * @var DateTimeImmutable
     */
    public $start;
    /**
     * @var int | string
     */
    public $competitionId;
    /**
     * @var string
     */
    public $competitionName;
    /**
     * @var string
     */
    public $home;
    /**
     * @var string
     */
    public $away;

    public function __construct()
    {
    }
}
