<?php
/**
 * Created by PhpStorm.
 * User: cdunnink
 * Date: 7-2-2017
 * Time: 09:49
 */

namespace App\Actions\Voetbal;

use App\Actions\Action;
use App\Response\ErrorResponse;
use JMS\Serializer\Serializer;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Voetbal\External\System\Repository as ExternalSystemRepository;
use Voetbal\External\System as ExternalSystem;

final class ExternalSystemAction extends Action
{
    /**
     * @var ExternalSystemRepository
     */
    protected $externalSystemRepos;
    /**
     * @var Serializer
     */
    protected $serializer;

    public function __construct(ExternalSystemRepository $externalSystemRepos, Serializer $serializer)
    {
        $this->externalSystemRepos = $externalSystemRepos;
        $this->serializer = $serializer;
    }

    public function fetch( Request $request, Response $response, $args ): Response
    {
        try {
            $externalSystems = $this->externalSystemRepos->findAll();

            $json = $this->serializer->serialize( $externalSystems, 'json');
            return $this->respondWithJson( $response, $json );
        }
        catch( \Exception $e ){
            return new ErrorResponse($e->getMessage(), 400);
        }
    }

    public function fetchOne( Request $request, Response $response, $args ): Response
    {
        try {
            $externalSystem = $this->externalSystemRepos->find((int) $args['id']);
            if ( $externalSystem === null ) {
                throw new \Exception("geen extern systeem met het opgegeven id gevonden", E_ERROR);
            }
            $json = $this->serializer->serialize( $externalSystem, 'json');
            return $this->respondWithJson( $response, $json );
        }
        catch( \Exception $e ){
            return new ErrorResponse($e->getMessage(), 400);
        }
    }

    public function add( Request $request, Response $response, $args ): Response
    {
        try {
            /** @var ExternalSystem $externalSystemSer */
            $externalSystemSer = $this->serializer->deserialize($this->getRawData(), 'Voetbal\External\System', 'json');

            $systemWithSameName = $this->externalSystemRepos->findOneBy( array('name' => $externalSystemSer->getName() ) );
            if ( $systemWithSameName !== null ){
                throw new \Exception("het externe systeem ".$externalSystemSer->getName()." bestaat al", E_ERROR );
            }

            $newExternalSystem = $this->externalSystemRepos->save( $externalSystemSer );

            $json = $this->serializer->serialize( $newExternalSystem, 'json');
            return $this->respondWithJson( $response, $json );
        }
        catch( \Exception $e ){
            return new ErrorResponse($e->getMessage(), 422);
        }
    }

    public function edit( Request $request, Response $response, $args ): Response
    {
        try {
            /** @var ExternalSystem $systemSer */
            $systemSer = $this->serializer->deserialize($this->getRawData(), 'Voetbal\External\System', 'json');

            $system = $this->externalSystemRepos->find($args['id']);
            if ( $system === null ) {
                throw new \Exception("het externe systeem kon niet gevonden worden o.b.v. de invoer", E_ERROR);
            }

            $systemSameName = $this->externalSystemRepos->findOneBy( array('name' => $systemSer->getName() ) );
            if ( $systemSameName !== null and $systemSameName !== $system ){
                throw new \Exception("het externe systeem met de naam ".$systemSer->getName()." bestaat al", E_ERROR );
            }

            $system->setName( $systemSer->getName() );
            $system->setWebsite( $systemSer->getWebsite() );
            $system->setUsername( $systemSer->getUsername() );
            $system->setPassword( $systemSer->getPassword() );
            $system->setApiurl( $systemSer->getApiurl() );
            $system->setApikey( $systemSer->getApikey() );
            $systemRet = $this->externalSystemRepos->save( $system );

            $json = $this->serializer->serialize( $systemRet, 'json');
            return $this->respondWithJson( $response, $json );
        }
        catch( \Exception $e ){
            return new ErrorResponse($e->getMessage(), 422);
        }
    }

    public function remove( Request $request, Response $response, $args ): Response
    {
        try{
            $association = $this->externalSystemRepos->find((int) $args['id']);
            if ( $association === null ) {
                throw new \Exception("geen extern systeem met het opgegeven id gevonden", E_ERROR);
            }
            $this->externalSystemRepos->remove( $association );
            return $response->withStatus(200);
        }
        catch( \Exception $e ){
            return new ErrorResponse( $e->getMessage(), 422);
        }
    }

}