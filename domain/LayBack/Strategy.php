<?php


namespace VOBetting\LayBack;

use DateTimeImmutable;
use VOBetting\LayBack;
use VOBetting\Strategy as StrategyBase;

class Strategy extends StrategyBase
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @param DateTimeImmutable $dateTime
     * @return array|LayBack[]
     */
    public function getLayBacks( DateTimeImmutable $dateTime): array {
        return [];
    }
}