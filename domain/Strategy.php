<?php

namespace VOBetting;

use DateTimeImmutable;
use League\Period\Period;
use VOBetting\BetLine\Repository as BetLineRepository;
use VOBetting\LayBack\Repository as LayBackRepository;

abstract class Strategy
{
    /**
     * @var BetLineRepository
     */
    protected $betLineRepos;
    /**
     * @var LayBackRepository
     */
    protected $layBackRepos;

    public function __construct(
        BetLineRepository $betLineRepos,
        LayBackRepository $layBackRepos )
    {
        $this->betLineRepos = $betLineRepos;
        $this->layBackRepos = $layBackRepos;
    }

    /**
     * @param Period $period
     * @return array|LayBack[]
     */
    abstract public function getLayBackCandidates( Period $period ): array;

    abstract public function addTransaction( Transaction $transaction ): void;
}
