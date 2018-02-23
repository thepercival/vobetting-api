<?php
/**
 * Created by PhpStorm.
 * User: coen
 * Date: 22-2-18
 * Time: 15:50
 */

namespace App\Action;

use JMS\Serializer\Serializer;
use Voetbal\Team\Service as TeamService;
use Voetbal\Team\Repository as TeamRepository;
use Voetbal\Game\Repository as GameRepository;
use Voetbal\Association\Repository as AssociationRepository;
use Voetbal;
use VOBetting\BetLine\Repository as BetLineRepository;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

final class BetLine
{
    /**
     * @var BetLineRepository
     */
    protected $repos;
    /**
     * @var GameRepository
     */
    protected $gameRepos;
    /**
     * @var Serializer
     */
    protected $serializer;

    public function __construct( BetLineRepository $repos, GameRepository $gameRepository, Serializer $serializer)
    {
        $this->repos = $repos;
        $this->gameRepos = $gameRepository;
        $this->serializer = $serializer;
    }

    public function fetch( $request, $response, $args)
    {
        $sErrorMessage = null;
        try {
            $gameid = (int) $request->getParam("gameid");
            $game = $this->gameRepos->find($gameid);
            if ( $game === null ) {
                throw new \Exception("er kan geen wedstrijd worden gevonden o.b.v. de invoergegevens", E_ERROR);
            }
            $filters = array( "game" => $game );
            $betType = (int) $request->getParam("bettype");
            if( $betType > 0 ) {
                $filters["betType"] = $betType;
            }
            $betLines = $this->repos->findBy( $filters );

            return $response
                ->withHeader('Content-Type', 'application/json;charset=utf-8')
                ->write($this->serializer->serialize( $betLines, 'json'));
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
        return $response->withStatus(404, 'geen wedregel met het opgegeven id gevonden');
    }
}