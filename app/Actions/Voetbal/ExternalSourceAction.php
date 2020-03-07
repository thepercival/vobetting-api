<?php
/**
 * Created by PhpStorm.
 * User: cdunnink
 * Date: 7-2-2017
 * Time: 09:49
 */

namespace App\Actions\Voetbal;

namespace App\Actions\Voetbal;

use App\Response\ErrorResponse;
use App\Response\ForbiddenResponse as ForbiddenResponse;
use Selective\Config\Configuration;
use App\Actions\Action;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Log\LoggerInterface;
use JMS\Serializer\SerializerInterface;
use Voetbal\ExternalSource\Repository as ExternalSourceRepository;
use Voetbal\ExternalSource;

final class ExternalSourceAction extends Action
{
    /**
     * @var ExternalSourceRepository
     */
    protected $externalSourceRepos;
    /**
     * @var SerializerInterface
     */
    protected $serializer;

    public function __construct(
        LoggerInterface $logger,
        SerializerInterface $serializer,
        ExternalSourceRepository $externalSourceRepos
    )
    {
        parent::__construct($logger,$serializer);
        $this->externalSourceRepos = $externalSourceRepos;
    }

    public function fetch( Request $request, Response $response, $args ): Response
    {
        try {
            $externalSources = $this->externalSourceRepos->findAll();

            $json = $this->serializer->serialize( $externalSources, 'json');
            return $this->respondWithJson( $response, $json );
        }
        catch( \Exception $e ){
            return new ErrorResponse($e->getMessage(), 400);
        }
    }

    public function fetchOne( Request $request, Response $response, $args ): Response
    {
        try {
            $externalSource = $this->externalSourceRepos->find((int) $args['id']);
            if ( $externalSource === null ) {
                throw new \Exception("geen extern systeem met het opgegeven id gevonden", E_ERROR);
            }
            $json = $this->serializer->serialize( $externalSource, 'json');
            return $this->respondWithJson( $response, $json );
        }
        catch( \Exception $e ){
            return new ErrorResponse($e->getMessage(), 400);
        }
    }

    public function add( Request $request, Response $response, $args ): Response
    {
        try {
            /** @var ExternalSource $externalSourceSer */
            $externalSourceSer = $this->serializer->deserialize($this->getRawData(), 'Voetbal\ExternalSource', 'json');

            $systemWithSameName = $this->externalSourceRepos->findOneBy( array('name' => $externalSourceSer->getName() ) );
            if ( $systemWithSameName !== null ){
                throw new \Exception("het externe systeem ".$externalSourceSer->getName()." bestaat al", E_ERROR );
            }

            $newExternalSource = $this->externalSourceRepos->save( $externalSourceSer );

            $json = $this->serializer->serialize( $newExternalSource, 'json');
            return $this->respondWithJson( $response, $json );
        }
        catch( \Exception $e ){
            return new ErrorResponse($e->getMessage(), 422);
        }
    }

    public function edit( Request $request, Response $response, $args ): Response
    {
        try {
            /** @var ExternalSource $externalSourceSer */
            $externalSourceSer = $this->serializer->deserialize($this->getRawData(), 'Voetbal\ExternalSource', 'json');

            $externalSource = $this->externalSourceRepos->find($args['id']);
            if ( $externalSource === null ) {
                throw new \Exception("het externe systeem kon niet gevonden worden o.b.v. de invoer", E_ERROR);
            }

            $systemSameName = $this->externalSourceRepos->findOneBy( array('name' => $externalSourceSer->getName() ) );
            if ( $systemSameName !== null and $systemSameName !== $externalSource ){
                throw new \Exception("het externe systeem met de naam ".$externalSourceSer->getName()." bestaat al", E_ERROR );
            }

            $externalSource->setName( $externalSourceSer->getName() );
            $externalSource->setWebsite( $externalSourceSer->getWebsite() );
            $externalSource->setUsername( $externalSourceSer->getUsername() );
            $externalSource->setPassword( $externalSourceSer->getPassword() );
            $externalSource->setApiurl( $externalSourceSer->getApiurl() );
            $externalSource->setApikey( $externalSourceSer->getApikey() );
            $externalSourceret = $this->externalSourceRepos->save( $externalSource );

            $json = $this->serializer->serialize( $externalSourceret, 'json');
            return $this->respondWithJson( $response, $json );
        }
        catch( \Exception $e ){
            return new ErrorResponse($e->getMessage(), 422);
        }
    }

    public function remove( Request $request, Response $response, $args ): Response
    {
        try{
            $externalSource = $this->externalSourceRepos->find((int) $args['id']);
            if ( $externalSource === null ) {
                throw new \Exception("geen extern systeem met het opgegeven id gevonden", E_ERROR);
            }
            $this->externalSourceRepos->remove( $externalSource );
            return $response->withStatus(200);
        }
        catch( \Exception $e ){
            return new ErrorResponse( $e->getMessage(), 422);
        }
    }

}