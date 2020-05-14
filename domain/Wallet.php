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
    protected $amount;
    /**
     * @var array|Transaction[]
     */
    protected $transactions;

    public function __construct( Range $currencyRange) {
        $this->currencyRange = $currencyRange;
        $this->transactions = array();
    }

    public function buy( LayBack $layBack ): Transaction {
        if( $layBack->getSize() < $this->currencyRange->min ) {
            throw new Exception("layback not bought, not over minimum", E_ERROR );
        }
        $sizeBought = $this->getSizeBought( $layBack->getBetLine() );
        if( $this->willMaxBeExceeded( $sizeBought, $layBack->getSize() ) ) {
            throw new Exception("layback not bought, maximum exceeded", E_ERROR );
        }
        $sizeBuy = $this->getSizeBuying( $sizeBought, $layBack->getSize() );
        $transaction = new Transaction( new DateTimeImmutable(), $layBack, $sizeBuy );
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
     * @return array|Transaction[]
     * @throws Exception
     */
    public function checkPayouts(): array {
        $now = new DateTimeImmutable();
        $transactionsToCheck = [];
        foreach( $this->transactions as $key => $transaction ) {
            $game = $transaction->getLayBack()->getBetLine()->getGame();
            if( $game->getState() === State::Finished && $game->getStartDateTime() > $now ) {
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
