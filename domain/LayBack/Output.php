<?php

namespace VOBetting\LayBack;

use LucidFrame\Console\ConsoleTable;
use stdClass;
use VOBetting\LayBack;
use Sports\NameService;
use Sports\Game;
use VOBetting\Output as AbstractOutput;

class Output extends AbstractOutput
{
    /**
     * @var array|LayBack[]
     */
    protected $layBacks;

    /**
     * Output constructor.
     * @param array|LayBack[] $layBacks
     */
    public function __construct(array $layBacks)
    {
        parent::__construct();
        $this->layBacks = $layBacks;
    }

    public function toConsole(): void
    {

        $converted = $this->convert();
        $this->order($converted);
        $table = new ConsoleTable();
        $table->setHeaders(array('competition', 'game', 'gameStart', 'bettype', 'runner', 'b/l', 'bookmaker', 'price', 'size', 'datetime' ));
        foreach( $converted as $convertedLayBack ) {
            $row = array(
                $convertedLayBack->competition,
                $convertedLayBack->game,
                $convertedLayBack->gameStart,
                $convertedLayBack->bettype,
                $convertedLayBack->runner,
                $convertedLayBack->backOrLay,
                $convertedLayBack->bookmaker,
                $convertedLayBack->price,
                $convertedLayBack->size,
                $convertedLayBack->datetime
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
        foreach( $this->layBacks as $layBack ) {
            $game = $layBack->getBetLine()->getGame();
            $convertedLayBack = new stdClass();
            $convertedLayBack->competition = $game->getRound()->getNumber()->getCompetition()->getName();
            $gameTitle = $nameService->getPlacesFromName($game->getPlaces(Game::HOME), true, true) . ' - ' . $nameService->getPlacesFromName($game->getPlaces(Game::AWAY), true, true);
            $convertedLayBack->game = $gameTitle;
            $convertedLayBack->gameStart = $game->getStartDateTime()->format(parent::DATEFORMAT );
            $convertedLayBack->bettype = $layBack->getBetLine()->getBetType();
            $convertedLayBack->runner = $layBack->getRunnerHomeAway() === Game::HOME ? 'home' : ( $layBack->getRunnerHomeAway() === Game::AWAY ? 'away' : 'draw' );
            $convertedLayBack->backOrLay = $layBack->getBack() ? "back" : "lay";
            $convertedLayBack->bookmaker = $layBack->getBookmaker()->getName();
            $convertedLayBack->price = $layBack->getPrice();
            $convertedLayBack->size = $layBack->getSize();
            $convertedLayBack->datetime = $layBack->getDateTime()->format(parent::DATEFORMAT );
            $converted[] = $convertedLayBack;
        }
        return $converted;
    }

    /**
     * @param array|stdClass[] $convertedLayBacks
     */
    protected function order(array &$convertedLayBacks) {
        uasort( $convertedLayBacks, function( stdClass $layBack1, stdClass $layBack2 ): int {
            $cmp = strcmp( $layBack1->competition, $layBack2->competition);
            if( $cmp !== 0 ) {
                return $cmp;
            }
            $cmp = strcmp( $layBack1->game, $layBack2->game);
            if( $cmp !== 0 ) {
                return $cmp;
            }
            $cmp = strcmp( $layBack1->bettype, $layBack2->bettype);
            if( $cmp !== 0 ) {
                return $cmp;
            }
            $cmp = strcmp( $layBack1->runner, $layBack2->runner);
            if( $cmp !== 0 ) {
                return $cmp;
            }
            $cmp = strcmp( $layBack1->backOrLay, $layBack2->backOrLay);
            if( $cmp !== 0 ) {
                return $cmp;
            }
            $cmp = strcmp( $layBack1->bookmaker, $layBack2->bookmaker);
            if( $cmp !== 0 ) {
                return $cmp;
            }
            $cmp = strcmp( $layBack1->price, $layBack2->price);
            if( $cmp !== 0 ) {
                return $cmp;
            }
            return strcmp( $layBack1->size, $layBack2->size);
        });
    }
}
