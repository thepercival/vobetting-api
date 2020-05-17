<?php
/**
 * Created by PhpStorm.
 * User: coen
 * Date: 28-1-17
 * Time: 20:44
 */

namespace VOBetting;

use DateTimeImmutable;

class Transaction
{
    /**
     * @var int
     */
    protected $action;
    /**
     * @var DateTimeImmutable
     */
    protected $dateTime;
    /**
     * @var LayBack
     */
    protected $layBack;
    /**
     * @var float
     */
    protected $size;
    public const BUY = 1;
    public const PAYOUT = 2;

    public function __construct( int $action, DateTimeImmutable $dateTime, LayBack $layBack, float $size) {
        $this->action = $action;
        $this->dateTime = $dateTime;
        $this->layBack = $layBack;
        $this->size = $size;
    }

    public function getDateTime(): DateTimeImmutable {
        return $this->dateTime;
    }

    public function getAction(): int {
        return $this->action;
    }

    public function getLayBack(): LayBack {
        return $this->layBack;
    }

    public function getSize(): float {
        return $this->size;
    }

    public function getPayout(): float {
        return $this->getSize() * $this->getLayBack()->getPrice();
    }
}
