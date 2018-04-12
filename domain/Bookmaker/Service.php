<?php
/**
 * Created by PhpStorm.
 * User: coen
 * Date: 12-4-18
 * Time: 11:23
 */

namespace VOBetting\Bookmaker;

use VOBetting\Bookmaker;
use VOBetting\Bookmaker\Repository as BookmakerRepository;

class Service
{
    /**
     * @var BookmakerRepository
     */
    protected $repos;

    /**
     * Service constructor.
     *
     * @param BookmakerRepository $repos
     */
    public function __construct( BookmakerRepository $repos )
    {
        $this->repos = $repos;
    }

    /**
     * @param string $name
     * @param bool $exchange
     * @return Bookmaker
     * @throws \Exception
     */
    public function create( string $name, bool $exchange ): Bookmaker
    {
        $bookmakerWithSameName = $this->repos->findOneBy( array('name' => $name ) );
        if ( $bookmakerWithSameName !== null ){
            throw new \Exception("de bookmaker met de naam ".$name." bestaat al", E_ERROR );
        }
        $bookmaker = new Bookmaker( $name, $exchange);
        return $this->repos->save($bookmaker);
    }

    /**
     * @param Bookmaker $bookmaker
     * @param string $name
     * @param bool $exchange
     * @return mixed
     * @throws \Exception
     */
    public function changeBasics( Bookmaker $bookmaker, string $name, bool $exchange )
    {
        $bookmakerWithSameName = $this->repos->findOneBy( array('name' => $name ) );
        if ( $bookmakerWithSameName !== null and $bookmakerWithSameName !== $bookmaker ){
            throw new \Exception("de bookmaker met de naam ".$name." bestaat al", E_ERROR );
        }
        $bookmaker->setName($name);
        $bookmaker->setExchange($exchange);
        return $this->repos->save($bookmaker);
    }

    /**
     * @param Bookmaker $bookmaker
     */
    public function remove( Bookmaker $bookmaker )
    {
        $this->repos->remove($bookmaker);
    }
}