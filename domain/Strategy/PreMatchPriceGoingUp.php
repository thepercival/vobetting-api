<?php


namespace VOBetting\Strategy;

use VOBetting\BetLine\Repository as BetLineRepository;
use VOBetting\LayBack\Repository as LayBackRepository;
use DateTimeImmutable;
use League\Period\Period;
use VOBetting\Bookmaker;
use VOBetting\LayBack;
use VOBetting\Strategy as StrategyBase;
use Voetbal\Game;
use Voetbal\Range;
use Exception;

class PreMatchPriceGoingUp extends StrategyBase
{
    /**
     * @var Period
     */
    protected $gamesPeriod;
    /**
     * @var array|Bookmaker[]
     */
    protected $baselineBookmakers;
    /**
     * @var int
     */
    protected $baselineDeltaPercentage;

    /**
     * PreMatchPriceGoingUp constructor.
     * @param BetLineRepository $betLineRepos,
     * @param LayBackRepository $layBackRepos,
     * @param Range $buyRangeHours
     * @param array $baselineBookmakers
     * @param int|null $baselineDeltaPercentage
     *
     * @throws Exception
     */
    public function __construct(
        BetLineRepository $betLineRepos,
        LayBackRepository $layBackRepos,
        Range $buyRangeHours,
        array $baselineBookmakers,
        int $baselineDeltaPercentage = null )
    {
        parent::__construct( $betLineRepos, $layBackRepos );
        $now = new DateTimeImmutable();
        $start = $now->modify("+".$buyRangeHours->min." hours");
        $end = $now->modify("+".$buyRangeHours->max." hours");
        try {
            $this->gamesPeriod =  new Period( $start, $end );
        } catch( Exception $e ) {}
        $this->baselineBookmakers = $baselineBookmakers;
        if( $baselineDeltaPercentage === null ) {
            $baselineDeltaPercentage = 0;
        }
        $this->baselineDeltaPercentage = $baselineDeltaPercentage;
    }

    /**
     * @param Period $period
     * @return array|LayBack[]
     */
    public function getLayBackCandidates( Period $period ): array {
        $candidates = [];

        $betLines = $this->betLineRepos->findByExt( $this->gamesPeriod );
        foreach( $betLines as $betLine ) {
            /** @var bool|null $runner */
            foreach( [Game::HOME,Game::AWAY, null] as $runner ) {
                $runnerLayBacks = $this->layBackRepos->findByExt( $betLine, $period, $runner );
                $baselinePrice = $this->getBaselinePrice( $runnerLayBacks );
                if( $baselinePrice === null ) {
                    continue;
                }
                /** @var LayBack $runnerLayBack */
                foreach( $runnerLayBacks as $runnerLayBack ) {
                    if( $this->priceIsLowEnough( $baselinePrice, $runnerLayBack->getPrice()) ) {
                        $candidates[] = $runnerLayBack;
                    }
                }
            }
        }

        return $candidates;
    }

    /**
     * @param array|LayBack[] $runnerLayBacks
     * @return float|null
     */
    protected function getBaselinePrice( array $runnerLayBacks): ?float {
        $totalSize = 0.0;
        $totalPrice = 0.0;
        foreach( $runnerLayBacks as $runnerLayBack ) {
            echo $runnerLayBack->getBookmaker()->getName();
            if( !in_array( $runnerLayBack->getBookmaker(), $this->baselineBookmakers, true) ) {
                continue;
            }
            $totalSize += $runnerLayBack->getSize();
            $totalPrice += $runnerLayBack->getSize() * $runnerLayBack->getPrice();
        }
        if( $totalPrice === 0.0 ) {
            return null;
        }
        return $totalPrice / $totalPrice;


    }

    protected function priceIsLowEnough( float $baseline, float $price ): bool {
        $changedBaseline = $baseline + ( ($baseline / 100) * $this->baselineDeltaPercentage);
        return $price < $changedBaseline;
    }
}