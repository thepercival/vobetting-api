<?php
/**
 * Created by PhpStorm.
 * User: coen
 * Date: 22-2-18
 * Time: 15:50
 */

namespace App\Action;

use JMS\Serializer\Serializer;
use VOBetting\Bookmaker\Repository as BookmakerRepository;
use VOBetting\Bookmaker\Service as BookmakerService;

final class Bookmaker
{
    /**
     * @var BookmakerRepository
     */
    protected $repos;
    /**
     * @var BookmakerService
     */
    protected $service;
    /**
     * @var Serializer
     */
    protected $serializer;

    public function __construct( BookmakerRepository $repos, BookmakerService $service, Serializer $serializer)
    {
        $this->repos = $repos;
        $this->service = $service;
        $this->serializer = $serializer;
    }

    public function fetch( $request, $response, $args)
    {
        $sErrorMessage = null;
        try {
            $bookmakers = $this->repos->findAll();

            return $response
                ->withHeader('Content-Type', 'application/json;charset=utf-8')
                ->write($this->serializer->serialize( $bookmakers, 'json'));
            ;
        }
        catch( \Exception $e ){
            $sErrorMessage = $e->getMessage();
        }
        return $response->withStatus(404)->write( $sErrorMessage );
    }

    public function fetchOne( $request, $response, $args)
    {
        $object = $this->repos->find($args['id']);
        if ($object) {
            return $response
                ->withHeader('Content-Type', 'application/json;charset=utf-8')
                ->write($this->serializer->serialize( $object, 'json'));
            ;
        }
        return $response->withStatus(404, 'geen bookmakers met het opgegeven id gevonden');
    }

    public function add( $request, $response, $args)
    {
        $sErrorMessage = null;
        try {
            /** @var \VOBetting\Bookmaker $bookmakerSer */
            $bookmakerSer = $this->serializer->deserialize(json_encode($request->getParsedBody()), 'VOBetting\Bookmaker', 'json');
            if ( $bookmakerSer === null ) {
                throw new \Exception("er kan geen bookmaker worden toegevoegd o.b.v. de invoergegevens", E_ERROR);
            }

            $bookmakerRet = $this->service->create( 
                $bookmakerSer->getName(), $bookmakerSer->getExchange() );
            
            return $response
                ->withStatus(201)
                ->withHeader('Content-Type', 'application/json;charset=utf-8')
                ->write($this->serializer->serialize( $bookmakerRet, 'json'));
            ;
        }
        catch( \Exception $e ){
            $sErrorMessage = $e->getMessage();
        }
        return $response->withStatus(404)->write( $sErrorMessage );
    }

    public function edit( $request, $response, $args)
    {
        $sErrorMessage = null;
        try {
            /** @var \VOBetting\Bookmaker $bookmakerSer */
            $bookmakerSer = $this->serializer->deserialize(json_encode($request->getParsedBody()), 'VOBetting\Bookmaker', 'json');
            if ( $bookmakerSer === null ) {
                throw new \Exception("er kan geen bookmaker worden gewijzigd o.b.v. de invoergegevens", E_ERROR);
            }

            $bookmaker = $this->repos->find($bookmakerSer->getId());
            if ( $bookmaker === null ) {
                throw new \Exception("de bookmaker kon niet gevonden worden o.b.v. de invoer", E_ERROR);
            }

            $bookmakerRet = $this->service->changeBasics(
                $bookmaker,
                $bookmakerSer->getName(),
                $bookmakerSer->getExchange()
            );

            return $response
                ->withStatus(201)
                ->withHeader('Content-Type', 'application/json;charset=utf-8')
                ->write($this->serializer->serialize( $bookmakerRet, 'json'));
            ;
        }
        catch( \Exception $e ){
            $sErrorMessage = $e->getMessage();
        }
        return $response->withStatus(404)->write( $sErrorMessage );
    }

    public function remove( $request, $response, $args)
    {
        $bookmaker = $this->repos->find($args['id']);
        $sErrorMessage = null;
        try {
            $this->service->remove($bookmaker);

            return $response
                ->withStatus(204);
            ;
        }
        catch( \Exception $e ){
            $sErrorMessage = $e->getMessage();
        }
        return $response->withStatus(404)->write( $sErrorMessage );
    }
}