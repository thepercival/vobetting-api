<?php
/**
 * Created by PhpStorm.
 * User: coen
 * Date: 22-5-18
 * Time: 12:23
 */

namespace App\Actions\Sports;

use App\Response\ErrorResponse;
use App\Response\ForbiddenResponse as ForbiddenResponse;
use Selective\Config\Configuration;
use App\Actions\Action;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Log\LoggerInterface;
use JMS\Serializer\SerializerInterface;
use Sports\Sport\Repository as SportRepository;
use Sports\Sport;

final class SportAction extends Action
{
    /**
     * @var SportRepository
     */
    private $sportRepos;
    /**
     * @var Configuration
     */
    protected $config;

    public function __construct(
        LoggerInterface $logger,
        SerializerInterface $serializer,
        SportRepository $sportRepos,
        Configuration $config
    ) {
        parent::__construct($logger, $serializer);
        $this->sportRepos = $sportRepos;
        $this->config = $config;
    }

    public function fetch(Request $request, Response $response, $args): Response
    {
        try {
            $sports = $this->sportRepos->findAll();

            $json = $this->serializer->serialize($sports, 'json');
            return $this->respondWithJson($response, $json);
        } catch (\Exception $e) {
            return new ErrorResponse($e->getMessage(), 400);
        }
    }


    public function fetchOne(Request $request, Response $response, $args): Response
    {
        try {
            $sport = $this->sportRepos->find((int) $args['id']);
            if ($sport === null) {
                throw new \Exception("geen sporten met het opgegeven id gevonden", E_ERROR);
            }
            $json = $this->serializer->serialize($sport, 'json');
            return $this->respondWithJson($response, $json);
        } catch (\Exception $e) {
            return new ErrorResponse($e->getMessage(), 400);
        }
    }

    public function add(Request $request, Response $response, $args): Response
    {
        try {
            /** @var \Sports\Sport $sportSer */
            $sportSer = $this->serializer->deserialize($this->getRawData(), 'Sports\Sport', 'json');

            $sportWithSameName = $this->sportRepos->findOneBy(array('name' => $sportSer->getName() ));
            if ($sportWithSameName !== null) {
                throw new \Exception("de sport met de naam ".$sportSer->getName()." bestaat al", E_ERROR);
            }

            $newSport = new Sport($sportSer->getName());
            $newSport->setTeam($sportSer->getTeam());
            $this->sportRepos->save($newSport);

            $json = $this->serializer->serialize($newSport, 'json');
            return $this->respondWithJson($response, $json);
        } catch (\Exception $e) {
            return new ErrorResponse($e->getMessage(), 422);
        }
    }

    public function edit(Request $request, Response $response, $args): Response
    {
        try {
            /** @var \Sports\Sport $sportSer */
            $sportSer = $this->serializer->deserialize($this->getRawData(), 'Sports\Sport', 'json');

            $sport = $this->sportRepos->find($args['id']);
            if ($sport === null) {
                throw new \Exception("de sport kon niet gevonden worden o.b.v. de invoer", E_ERROR);
            }

            $sportWithSameName = $this->sportRepos->findOneBy(array('name' => $sportSer->getName() ));
            if ($sportWithSameName !== null and $sportWithSameName !== $sport) {
                throw new \Exception("de sport met de naam ".$sportSer->getName()." bestaat al", E_ERROR);
            }

            $sport->setName($sportSer->getName());
            $sport->setTeam($sportSer->getTeam());
            $this->sportRepos->save($sport);

            $json = $this->serializer->serialize($sport, 'json');
            return $this->respondWithJson($response, $json);
        } catch (\Exception $e) {
            return new ErrorResponse($e->getMessage(), 422);
        }
    }

    public function remove(Request $request, Response $response, $args): Response
    {
        try {
            $sport = $this->sportRepos->find((int) $args['id']);
            if ($sport === null) {
                throw new \Exception("geen sport met het opgegeven id gevonden", E_ERROR);
            }
            $this->sportRepos->remove($sport);
            return $response->withStatus(200);
        } catch (\Exception $e) {
            return new ErrorResponse($e->getMessage(), 422);
        }
    }
}
