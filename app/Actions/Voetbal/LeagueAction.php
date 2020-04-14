<?php
/**
 * Created by PhpStorm.
 * User: coen
 * Date: 22-5-18
 * Time: 12:23
 */

namespace App\Actions\Voetbal;

use App\Response\ErrorResponse;
use App\Response\ForbiddenResponse as ForbiddenResponse;
use Selective\Config\Configuration;
use App\Actions\Action;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Log\LoggerInterface;
use JMS\Serializer\SerializerInterface;
use Voetbal\League;
use Voetbal\League\Repository as LeagueRepository;
use Voetbal\Association\Repository as AssociationRepository;
use Voetbal\Association;

final class LeagueAction extends Action
{
    /**
     * @var LeagueRepository
     */
    private $leagueRepos;
    /**
     * @var AssociationRepository
     */
    private $associationRepos;
    /**
     * @var Configuration
     */
    protected $config;

    public function __construct(
        LoggerInterface $logger,
        SerializerInterface $serializer,
        LeagueRepository $leagueRepos,
        AssociationRepository $associationRepos,
        Configuration $config
    )
    {
        parent::__construct($logger,$serializer);
        $this->leagueRepos = $leagueRepos;
        $this->associationRepos = $associationRepos;
        $this->config = $config;
    }

    public function fetch( Request $request, Response $response, $args ): Response
    {
        try {
            $filter = [];
            {
                $queryParams = $request->getQueryParams();
                if (array_key_exists("associationId", $queryParams) && strlen($queryParams["associationId"]) > 0) {
                    $association = $this->associationRepos->find( $queryParams["associationId"] );
                    $filter["association"] = $association;
                }
            }
            $leagues = $this->leagueRepos->findBy( $filter );

            $json = $this->serializer->serialize( $leagues, 'json');
            return $this->respondWithJson( $response, $json );
        }
        catch( \Exception $e ){
            return new ErrorResponse($e->getMessage(), 400);
        }
    }


    public function fetchOne( Request $request, Response $response, $args ): Response
    {
        try {
            $league = $this->leagueRepos->find((int) $args['id']);
            if ( $league === null ) {
                throw new \Exception("geen competitie met het opgegeven id gevonden", E_ERROR);
            }
            $json = $this->serializer->serialize( $league, 'json');
            return $this->respondWithJson( $response, $json );
        }
        catch( \Exception $e ){
            return new ErrorResponse($e->getMessage(), 400);
        }
    }

    public function add( Request $request, Response $response, $args ): Response
    {
        try {
            /** @var \Voetbal\League $leagueSer */
            $leagueSer = $this->serializer->deserialize($this->getRawData(), 'Voetbal\League', 'json');

            $association = $this->associationRepos->findOneBy( [ "name" => $leagueSer->getAssociation()->getName()] );
            if ( $association === null ) {
                throw new \Exception("de bond kon niet gevonden worden o.b.v. de invoer", E_ERROR);
            }

            $leagueWithSameName = $this->leagueRepos->findOneBy( array('name' => $leagueSer->getName() ) );
            if ( $leagueWithSameName !== null ){
                throw new \Exception("de competitie met de naam ".$leagueSer->getName()." bestaat al", E_ERROR );
            }

            $newLeague = new League( $association, $leagueSer->getName() );
            $newLeague->setAbbreviation( $leagueSer->getAbbreviation() );

            $this->leagueRepos->save( $newLeague );

            $json = $this->serializer->serialize( $newLeague, 'json');
            return $this->respondWithJson( $response, $json );
        }
        catch( \Exception $e ){
            return new ErrorResponse($e->getMessage(), 422);
        }
    }

    public function edit( Request $request, Response $response, $args ): Response
    {
        try {
            /** @var \Voetbal\League $leagueSer */
            $leagueSer = $this->serializer->deserialize($this->getRawData(), 'Voetbal\League', 'json');

            $league = $this->leagueRepos->find($args['id']);
            if ( $league === null ) {
                throw new \Exception("de competitie kon niet gevonden worden o.b.v. de invoer", E_ERROR);
            }

            // can association be updated
//            $association = $this->associationRepos->findOneBy( [ "name" => $leagueSer->getAssociation()->getName()] );
//            if ( $association === null ) {
//                throw new \Exception("de bond kon niet gevonden worden o.b.v. de invoer", E_ERROR);
//            }

            $leagueWithSameName = $this->leagueRepos->findOneBy( array('name' => $leagueSer->getName() ) );
            if ( $leagueWithSameName !== null and $leagueWithSameName !== $league ){
                throw new \Exception("de competitie met de naam ".$leagueSer->getName()." bestaat al", E_ERROR );
            }

            $league->setName( $leagueSer->getName() );
            $league->setAbbreviation( $leagueSer->getAbbreviation() );
            // $league->setAssociation( $association );
            $this->leagueRepos->save( $league );

            $json = $this->serializer->serialize( $league, 'json');
            return $this->respondWithJson( $response, $json );
        }
        catch( \Exception $e ){
            return new ErrorResponse($e->getMessage(), 422);
        }
    }

    public function remove( Request $request, Response $response, $args ): Response
    {
        try{
            $league = $this->leagueRepos->find((int) $args['id']);
            if ( $league === null ) {
                throw new \Exception("geen competitie met het opgegeven id gevonden", E_ERROR);
            }
            $this->leagueRepos->remove( $league );
            return $response->withStatus(200);
        }
        catch( \Exception $e ){
            return new ErrorResponse( $e->getMessage(), 422);
        }
    }
}