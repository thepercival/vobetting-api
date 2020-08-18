<?php

namespace VOBetting\Transaction;

use LucidFrame\Console\ConsoleTable;
use stdClass;
use VOBetting\Transaction;
use Sports\NameService;
use Sports\Game;

use VOBetting\Output as AbstractOutput;

class Output extends AbstractOutput
{
    // verschillende weergaves en sorteringen
    // 1 alle transactions


    /**
     * @var array|Transaction[]
     */
    protected $transactions;

    /**
     * Output constructor.
     * @param array|Transaction[] $transactions
     */
    public function __construct(array $transactions)
    {
        parent::__construct();
        $this->transactions = $transactions;
    }

    public function toConsole(): void
    {
        $converted = $this->convert();
        $this->order($converted);
        $table = new ConsoleTable();
        $table->setHeaders(array('action', 'datetime', 'game', 'gameStart', 'runner', 'b/l', 'bookmaker', 'fee', 'price', 'size' ));
        foreach( $converted as $convertedTransaction ) {
            $row = array(
                $convertedTransaction->action === Transaction::BUY ? 'buy' : 'payout',
                $convertedTransaction->datetime,
                $convertedTransaction->game,
                $convertedTransaction->gameStart,
                $convertedTransaction->runner,
                $convertedTransaction->backOrLay,
                $convertedTransaction->bookmaker,
                $convertedTransaction->bookmakerFee,
                $convertedTransaction->price,
                $convertedTransaction->size
            );
            $table->addRow( $row );
        }
        $table->display();
    }

    /**
     * @return array|stdClass[]
     */
    protected function convert(): array {
        $converted = [];
        $nameService = new NameService();
        foreach( $this->transactions as $transaction ) {
            $layBack = $transaction->getLayBack();
            $game = $layBack->getBetLine()->getGame();
            $convertedTransaction = new stdClass();
            $convertedTransaction->action = $transaction->getAction();
            $convertedTransaction->datetime = $transaction->getDateTime()->format(parent::DATEFORMAT );
            $gameTitle = $nameService->getPlacesFromName($game->getPlaces(Game::HOME), true, true) . ' - ' . $nameService->getPlacesFromName($game->getPlaces(Game::AWAY), true, true);
            $convertedTransaction->game = $gameTitle;
            $convertedTransaction->gameStart = $game->getStartDateTime()->format(parent::DATEFORMAT );
            $convertedTransaction->runner = $layBack->getRunnerHomeAway() === Game::HOME ? 'home' : ( $layBack->getRunnerHomeAway() === Game::AWAY ? 'away' : 'draw' );
            $convertedTransaction->backOrLay = $layBack->getBack() ? "back" : "lay";
            $convertedTransaction->bookmaker = $layBack->getBookmaker()->getName();
            $convertedTransaction->bookmakerFee = $layBack->getBookmaker()->getFeePercentage() . '%';
            $convertedTransaction->price = $layBack->getPrice();
            $convertedTransaction->size = $transaction->getSize();

            $converted[] = $convertedTransaction;
        }
        return $converted;
    }

    /**
     * @param array|stdClass[] $convertedTransactions
     */
    protected function order(array &$convertedTransactions) {
//        uasort( $convertedTransactions, function( stdClass $transaction1, stdClass $transaction2 ): int {
//            $cmp = strcmp( $transaction1->competition, $transaction2->competition);
//            if( $cmp !== 0 ) {
//                return $cmp;
//            }
//            $cmp = strcmp( $transaction1->game, $transaction2->game);
//            if( $cmp !== 0 ) {
//                return $cmp;
//            }
//            $cmp = strcmp( $transaction1->bettype, $transaction2->bettype);
//            if( $cmp !== 0 ) {
//                return $cmp;
//            }
//            $cmp = strcmp( $transaction1->runner, $transaction2->runner);
//            if( $cmp !== 0 ) {
//                return $cmp;
//            }
//            $cmp = strcmp( $transaction1->backOrLay, $transaction2->backOrLay);
//            if( $cmp !== 0 ) {
//                return $cmp;
//            }
//            $cmp = strcmp( $transaction1->bookmaker, $transaction2->bookmaker);
//            if( $cmp !== 0 ) {
//                return $cmp;
//            }
//            $cmp = strcmp( $transaction1->price, $transaction2->price);
//            if( $cmp !== 0 ) {
//                return $cmp;
//            }
//            return strcmp( $transaction1->size, $transaction2->size);
//        });
    }
}
