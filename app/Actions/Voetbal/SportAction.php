<?php
/**
 * Created by PhpStorm.
 * User: coen
 * Date: 14-11-17
 * Time: 14:02
 */

namespace App\Actions\Voetbal;

use App\Response\ErrorResponse;
use Psr\Log\LoggerInterface;
use JMS\Serializer\SerializerInterface;
use Voetbal\Sport\Repository as SportRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Actions\Action;
use Voetbal\Sport;

final class SportAction extends Action
{
    /**
     * @var SportRepository
     */
    protected $sportRepos;

    public function __construct(
        LoggerInterface $logger,
        SerializerInterface $serializer,
        SportRepository $sportRepos
    )
    {
        parent::__construct($logger,$serializer);
        $this->sportRepos = $sportRepos;
    }

    public function fetchOne( Request $request, Response $response, $args ): Response
    {
        $sport = $this->sportRepos->findOneBy(["customId" => (int)$args['sportCustomId']]);
        if ( $sport === null ) {
            throw new \Exception("geen sport met het opgegeven id gevonden", E_ERROR);
        }

        $json = $this->serializer->serialize( $sport, 'json');
        return $this->respondWithJson($response, $json);
    }

    public function add( Request $request, Response $response, $args ): Response
    {
        try {
            /** @var \Voetbal\Sport $sport */
            $sport = $this->serializer->deserialize( $this->getRawData(), 'Voetbal\Sport', 'json');

            // check if name exists, else create sport
            $newSport = $this->sportRepos->findOneBy( ["name" => $sport->getName() ] );

            if( $newSport === null ) {
                $newSport = new Sport( $sport->getName() );
                $newSport->setTeam($sport->getTeam());
                $newSport->setCustomId($sport->getCustomId());
                $this->sportRepos->save($newSport);
            }
            $this->sportRepos->save( $newSport );

            $json = $this->serializer->serialize( $newSport, 'json');
            return $this->respondWithJson($response, $json);
        }
        catch( \Exception $e ){
            return new ErrorResponse($e->getMessage(), 422);
        }
    }
}