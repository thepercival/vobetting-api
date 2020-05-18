<?php

namespace VOBetting\Strategy;

use VOBetting\LayBack\Organizer as LayBackOrganizer;
use VOBetting\BetLine\Repository as BetLineRepository;
use VOBetting\LayBack\Repository as LayBackRepository;
use DateTimeImmutable;
use League\Period\Period;
use VOBetting\Bookmaker;
use VOBetting\LayBack;
use VOBetting\Strategy as StrategyBase;
use VOBetting\Transaction;
use Voetbal\Game;
use Voetbal\Range;
use Exception;

/**
 * // Commission charged = ((stake * odds) - stake) * commission rate
 *
 * Class PreMatchPriceGoingUp
 * @package VOBetting\Strategy
 */
class PreMatchPriceGoingUp extends StrategyBase
{
    /**
     * @var int
     */
    protected $profitPercentage;
    /**
     * @var Range
     */
    protected $layHourRange;
    /**
     * @var Range
     */
    protected $backHourRange;
    /**
     * @var array|Bookmaker[]
     */
    protected $baselineBookmakers;
    /**
     * @var int
     */
    protected $baselineDeltaPercentage;
    /**
     * @var array
     */
    protected $laysPerGame = [];

    /**
     * @param BetLineRepository $betLineRepos,
     * @param LayBackRepository $layBackRepos,
     * @param Range $layHourRange
     * @param Range $backHourRange
     * @param array $baselineBookmakers
     * @param int $profitPercentage
     * @param int $baselineDeltaPercentage
     *
     * @throws Exception
     */
    public function __construct(
        BetLineRepository $betLineRepos,
        LayBackRepository $layBackRepos,
        Range $layHourRange,
        Range $backHourRange,
        array $baselineBookmakers,
        int $profitPercentage,
        int $baselineDeltaPercentage )
    {
        parent::__construct( $betLineRepos, $layBackRepos );
        $this->layHourRange = $layHourRange;
        $this->backHourRange = $backHourRange;

        $this->baselineBookmakers = $baselineBookmakers;

        $this->profitPercentage = $profitPercentage;
        $this->baselineDeltaPercentage = $baselineDeltaPercentage;
    }

    /**
     * @param Period $period
     * @return array|LayBack[]
     */
    public function getLayBackCandidates( Period $period ): array {
        $candidates = $this->getLayCandidates($period);
        return array_merge( $candidates, $this->getBackCandidates($period) );
    }

    /**
     * Als de prijs van de lay van een exchange lager is dan de back van een bookmaker
     * dan is het een lay candidate
     *
     * @param Period $period
     * @return array|LayBack[]
     */
    protected function getLayCandidates( Period $period ): array {
        $candidates = [];

        $gamesPeriod = $this->getLayPeriod( $period->getEndDate() );
        try {
            $betLines = $this->betLineRepos->findByExt( $gamesPeriod );
            foreach( $betLines as $betLine ) {

                $layBackOrganizer = new LayBackOrganizer( $this->layBackRepos->findByExt( $betLine, $period ) );
                /** @var bool|null $runner */
                foreach( [Game::HOME,Game::AWAY, null] as $runner ) {

                    // @TODO CHANGE STRAT: FAVORITES GO DOWN, REVERSE FOR OTHER

                    // @TODO get highest back in past(for, betline, runner, $layOrBack, $exchange=true, if highest-back is x% more than candidate-lay continue
                    // @TODO wait with candidates until lay is rising!, determine average of current and previous and compare!!

                    $backs = $layBackOrganizer->get( $runner, LayBack::BACK, false );
                    $baselinePrice = $this->getBaselinePrice( $backs );
                    if( $baselinePrice === null ) {
                        continue;
                    }
                    if ( $betLine->getGame()->getId() == 3899 ) {
                        $baselinePrice = $this->getBaselinePrice( $backs );
                    }
                    $lays = $layBackOrganizer->get( $runner, LayBack::LAY, true );
                    $correctLays = array_filter( $lays, function( LayBack $lay ) use ($baselinePrice) : bool {
                        return $this->layPriceIsLowEnough( $baselinePrice, $lay->getPrice() + $lay->getFee() );
                    });
                    if( count( $correctLays ) > 0 ) {
                        (new LayBack\Output($correctLays))->toConsole();
                        (new LayBack\Output(array_merge($backs, $lays)))->toConsole();
                    }
                    $candidates = array_merge( $candidates, $correctLays );
                }
            }
        } catch( Exception $e ) {}

        $this->orderPriceAsc($candidates);
        return $candidates;
    }

    /**
     * Als de prijs van de back van een exchange hoger is dan de aangekochte lay incl. fee,
     * dan is het een back candidate
     *
     * @param Period $period
     * @return array|LayBack[]
     */
    protected function getBackCandidates( Period $period ): array {
        $candidates = [];

        $gamesPeriod = $this->getBackPeriod( $period->getEndDate() );
        try {
            $betLines = $this->betLineRepos->findByExt( $gamesPeriod );
            foreach( $betLines as $betLine ) {
                $layBackOrganizer = new LayBackOrganizer( $this->layBackRepos->findByExt( $betLine, $period ) );
                /** @var bool|null $runner */
                foreach( [Game::HOME,Game::AWAY, null] as $runner ) {
                    $backs = $layBackOrganizer->get( $runner, LayBack::BACK, true );
                    if ( $betLine->getGame()->getId() == 3899 ) {
                        $e = 4;
                    }
                    $correctLays = array_filter( $backs, function( LayBack $back ): bool {
                        $lay = $this->getLay( $back );
                        return $lay !== null && $this->backPriceIsHighEnough( $lay, $back );
                    });
                    $candidates = array_merge( $candidates, $correctLays );
                }
            }
        } catch( Exception $e ) {}

        $this->orderPriceDesc($candidates);
        return $candidates;
    }

    protected function getLay( LayBack $runnerLayBack): ?LayBack {
        $game = $runnerLayBack->getBetLine()->getGame();
        if( array_key_exists( $game->getId(), $this->laysPerGame) ) {
            $layBacksPerGame = $this->laysPerGame[$game->getId()];
            $runnerDescription = $this->getRunnerDescription( $runnerLayBack->getRunnerHomeAway() );
            if( array_key_exists( $runnerDescription, $layBacksPerGame) ) {
                return $layBacksPerGame[$runnerDescription];
            }
        }
        return null;
    }

    protected function getBaselinePrice( array $backs ): ?float {
        $nrOfBookmakers = 0;
        $totalPrice = 0.0;
        foreach( $backs as $back ) {
            if( $this->isBookmakerForBaselinePrice( $back->getBookmaker() ) === false ) {
                continue;
            }
            $totalPrice += $back->getPrice();
            $nrOfBookmakers++;
        }
        if( $totalPrice === 0.0 ) {
            return null;
        }
        return $totalPrice / $nrOfBookmakers;
    }

    protected function isBookmakerForBaselinePrice( Bookmaker $bookmaker ): bool {
        foreach( $this->baselineBookmakers as $baselineBookmaker ) {
            if( $baselineBookmaker->getId() === $bookmaker->getId() ) {
                return true;
            }
        }
        return false;
    }

    protected function getLayPeriod( DateTimeImmutable $currentDateTime ): Period {
        return new Period(
            $currentDateTime->modify("+".$this->layHourRange->max." hours"),
            $currentDateTime->modify("+".$this->layHourRange->min." hours")
        );
    }

    protected function getBackPeriod( DateTimeImmutable $currentDateTime ): Period {
        return new Period(
            $currentDateTime->modify("+".$this->backHourRange->max." hours"),
            $currentDateTime->modify("+".$this->backHourRange->min." hours")
        );
    }

    protected function layPriceIsLowEnough( float $baseline, float $price ): bool {
        $changedBaseline = $baseline + ( ($baseline / 100) * $this->baselineDeltaPercentage);
        return $price < $changedBaseline;
    }

    protected function backPriceIsHighEnough( LayBack $lay, LayBack $back ): bool {
        $feePercentage = $lay->getBookmaker()->getFeePercentage();
        if( $back->getBookmaker()->getFeePercentage() > $feePercentage ) {
            $feePercentage = $back->getBookmaker()->getFeePercentage();
        }
        $percentage = $feePercentage + $this->profitPercentage;
        $extra =  ( ( $back->getPrice() / 100 ) * $percentage );
        return ( $back->getPrice() - $extra ) >= $lay->getPrice();
    }

    /**
     * @param array|LayBack[] $layBacks
     */
    protected function orderPriceAsc(array &$layBacks ) {
        uasort( $layBacks, function( LayBack $layBack1, LayBack $layBack2 ): int {
            return $layBack1->getPrice() > $layBack2->getPrice() ? 1 : -1;
        });
    }

    /**
     * @param array|LayBack[] $layBacks
     */
    protected function orderPriceDesc(array &$layBacks ) {
        uasort( $layBacks, function( LayBack $layBack1, LayBack $layBack2 ): int {
            return $layBack1->getPrice() < $layBack2->getPrice() ? 1 : -1;
        });
    }

    public function addTransaction( Transaction $transaction ): void {
        $layBack = $transaction->getLayBack();
        $game = $layBack->getBetLine()->getGame();
        $runnerDescription = $this->getRunnerDescription( $layBack->getRunnerHomeAway() );
        $this->laysPerGame[$game->getId()][$runnerDescription] = $layBack;
    }

    protected function getRunnerDescription( bool $runner = null ): string {
        if( $runner === true ) {
            return 'thuis';
        } else if( $runner === false ) {
            return 'uit';
        }
        return 'gelijk';
    }
}