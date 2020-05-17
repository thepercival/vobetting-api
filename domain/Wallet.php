<?php
/**
 * Created by PhpStorm.
 * User: coen
 * Date: 28-1-17
 * Time: 20:44
 */

namespace VOBetting;

use DateTimeImmutable;
use Exception;
use Voetbal\Game;
use Voetbal\Range;
use Voetbal\State;

class Wallet
{
    /**
     * @var Range
     */
    protected $currencyRange;
    /**
     * @var float
     */
    protected $amount = 0.0;
    /**
     * @var array|Transaction[]
     */
    protected $transactions;

    public function __construct( Range $currencyRange) {
        $this->currencyRange = $currencyRange;
        $this->transactions = array();
    }

    public function buy( LayBack $layBack, DateTimeImmutable $currentDateTime ): ?Transaction {
        if( $layBack->getSize() < $this->currencyRange->min ) {
            throw new Exception("layback not bought, not over minimum", E_ERROR );
        }
        $sizeBought = $this->getSizeBought( $layBack->getBetLine() );

        if( $sizeBought >= ( $this->currencyRange->max - $this->currencyRange->min)  ) {
            throw new Exception("layback not bought, already or almost on maximum", E_ERROR );
        }
        $sizeBuy = $this->getSizeBuying( $sizeBought, $layBack->getSize() );
        if( $sizeBuy == 0 ) {
            return null;
        }
        $transaction = new Transaction( Transaction::BUY, $currentDateTime, $layBack, $sizeBuy );
        $this->transactions[] = $transaction;
        return $transaction;
    }

    protected function getSizeBought( BetLine $betLine): float {
        $transactions = array_filter( $this->transactions, function( Transaction $transaction ) use ($betLine) : bool {
            return $betLine->getGame() === $transaction->getLayBack()->getBetLine()->getGame()
                && $betLine->getBetType() === $transaction->getLayBack()->getBetLine()->getBetType();
        });
        return array_sum(
            array_map( function( Transaction $transaction): float {
                return $transaction->getSize();
            }, $transactions)
        );
    }

    protected function getSizeBuying( float $sizeBought, float $size ): float {
        $maxSizeToBuy = $this->currencyRange->max - $sizeBought;
        if( $size > $maxSizeToBuy ) {
            return $maxSizeToBuy;
        }
        return $size;
    }

    protected function willMaxBeExceeded( float $sizeBought, float $size): bool {
        return ($sizeBought + $size) > $this->currencyRange->max;
    }

    /**
     * @param DateTimeImmutable $currentDateTime
     * @return array|Transaction[]
     */
    public function checkPayouts( DateTimeImmutable $currentDateTime): array {
        $transactionsToCheck = [];
        foreach( $this->transactions as $key => $transaction ) {
            $game = $transaction->getLayBack()->getBetLine()->getGame();
            if( $game->getState() === State::Finished && $game->getStartDateTime() > $currentDateTime ) {
                $transactionsToCheck[] = $transaction;
            }
        }
        // remove from transaction
        foreach( $transactionsToCheck as $transactionToCheck ) {
            $this->payout($transactionToCheck);
        }
        return $transactionsToCheck;
    }

    public function payout( Transaction $transaction ) {
        $key = array_search( $transaction, $this->transactions, true );
        if( $key === false ) {
            return;
        }
        if( $transaction->getLayBack()->isWinner() ) {
            $this->amount += $transaction->getPayout();
        }
        array_splice( $this->transactions, $key, 1 );
    }

    /**
     * @return array|Transaction[]
     */
    public function getTransactions(): array {
        return $this->transactions;
    }

    public function getAmount(): float {
        return $this->amount;
    }
}
