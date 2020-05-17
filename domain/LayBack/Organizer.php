<?php


namespace VOBetting\LayBack;


use VOBetting\LayBack;
use Voetbal\Game;

class Organizer
{
    /**
     * @var array|LayBack[]
     */
    // protected $layBacks;
    /**
     * @var array|LayBack[][]
     */
    protected $dictonaryGet = [];

    /**
     * @param array|LayBack[] $layBacks
     */
    public function __construct(array $layBacks)
    {
        $this->init( $layBacks );
    }

    protected function init( $layBacks ) {
        $this->dictonaryGet = [];
        foreach( $layBacks as $layBack ) {
            $key = $this->getKey( $layBack->getBack(), $layBack->getBookmaker()->getExchange(), $layBack->getRunnerHomeAway() );
            if( array_key_exists( $key, $this->dictonaryGet) === false ) {
                $this->dictonaryGet[$key] = [];
            }
            $this->dictonaryGet[$key][]= $layBack;
        }
    }

    protected function getKey( bool $layOrBack, bool $exchange, bool $runner = null ): string {
        $key = $layOrBack === LayBack::BACK ? 'back' : 'lay';
        $key .= $exchange  ? '-exchange-' : '-bookmaker-';
        $key .= $runner === null ? 'allrunners' : ( $runner === Game::HOME ? 'home' : 'away' );
        return $key;
    }

    public function get( bool $runner = null , bool $layOrBack = null, bool $exchange = null ): array {
        $key = $this->getKey($layOrBack, $exchange, $runner);
        if( array_key_exists( $key, $this->dictonaryGet) === false ) {
            return [];
        }
        return $this->dictonaryGet[ $key ];
        /*return array_filter( $this->layBacks, function ( LayBack $layBack ) use ( $runner, $layOrBack, $exchange ): bool {
            if( $layOrBack !== null && $layOrBack !== $layBack->getBack() ) {
                return false;
            }
            if( $exchange !== null && $exchange !== $layBack->getBookmaker()->getExchange() ) {
                return false;
            }
            return $layBack->getRunnerHomeAway() === $runner;
        });*/
    }
}