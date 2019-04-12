<?php
/**
 * Created by PhpStorm.
 * User: coen
 * Date: 22-2-18
 * Time: 15:50
 */

namespace App\Action;

use JMS\Serializer\Serializer;
use VOBetting\BetLine\Repository as BetLineRepository;
use VOBetting\LayBack\Repository as LayBackRepository;

final class LayBack
{
    /**
     * @var LayBackRepository
     */
    protected $repos;
    /**
     * @var BetLineRepository
     */
    protected $betLineRepos;
    /**
     * @var Serializer
     */
    protected $serializer;

    public function __construct( LayBackRepository $repos, BetLineRepository $betLineRepository, Serializer $serializer)
    {
        $this->repos = $repos;
        $this->betLineRepos = $betLineRepository;
        $this->serializer = $serializer;
    }

    public function fetch( $request, $response, $args)
    {
        $sErrorMessage = null;
        try {
            $betLineid = (int) $request->getParam("betlineid");
            $betLine = $this->betLineRepos->find($betLineid);
            if ( $betLine === null ) {
                throw new \Exception("er kan geen wedregel worden gevonden o.b.v. de invoergegevens", E_ERROR);
            }
            $filters = array( "betLine" => $betLine );
            $layBacks = $this->repos->findBy( $filters );

            return $response
                ->withHeader('Content-Type', 'application/json;charset=utf-8')
                ->write($this->serializer->serialize( $layBacks, 'json'));
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
        return $response->withStatus(404, 'geen layback met het opgegeven id gevonden');
    }


}