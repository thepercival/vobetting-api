<?php
/**
 * Created by PhpStorm.
 * User: coen
 * Date: 22-5-18
 * Time: 12:23
 */

namespace App\Actions;

use App\Response\ErrorResponse;
use App\Response\ForbiddenResponse as ForbiddenResponse;
use Selective\Config\Configuration;
use Slim\Factory\ServerRequestCreatorFactory;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Log\LoggerInterface;
use JMS\Serializer\SerializerInterface;
use VOBetting\Bookmaker\Repository as BookmakerRepository;
use VOBetting\Bookmaker;

final class BookmakerAction extends Action
{
    /**
     * @var BookmakerRepository
     */
    private $bookmakerRepos;
    /**
     * @var Configuration
     */
    protected $config;

    public function __construct(
        LoggerInterface $logger,
        SerializerInterface $serializer,
        BookmakerRepository $bookmakerRepos,
        Configuration $config
    ) {
        parent::__construct($logger, $serializer);
        $this->bookmakerRepos = $bookmakerRepos;
        $this->config = $config;
    }

    public function fetch(Request $request, Response $response, $args): Response
    {
        try {
            $bookmakers = $this->bookmakerRepos->findAll();

            $json = $this->serializer->serialize($bookmakers, 'json');
            return $this->respondWithJson($response, $json);
        } catch (\Exception $e) {
            return new ErrorResponse($e->getMessage(), 400);
        }
    }


    public function fetchOne(Request $request, Response $response, $args): Response
    {
        try {
            $bookmaker = $this->bookmakerRepos->find((int) $args['id']);
            if ($bookmaker === null) {
                throw new \Exception("geen bookmakers met het opgegeven id gevonden", E_ERROR);
            }
            $json = $this->serializer->serialize($bookmaker, 'json');
            return $this->respondWithJson($response, $json);
        } catch (\Exception $e) {
            return new ErrorResponse($e->getMessage(), 400);
        }
    }

    public function add(Request $request, Response $response, $args): Response
    {
        try {
            /** @var Bookmaker $bookmakerSer */
            $bookmakerSer = $this->serializer->deserialize($this->getRawData(), 'VOBetting\Bookmaker', 'json');

            $bookmakerWithSameName = $this->bookmakerRepos->findOneBy(array('name' => $bookmakerSer->getName() ));
            if ($bookmakerWithSameName !== null) {
                throw new \Exception("de bookmaker met de naam ".$bookmakerSer->getName()." bestaat al", E_ERROR);
            }

            $newBookmaker = new \VOBetting\Bookmaker($bookmakerSer->getName(), $bookmakerSer->getExchange());
            $this->bookmakerRepos->save($newBookmaker);

            $json = $this->serializer->serialize($newBookmaker, 'json');
            return $this->respondWithJson($response, $json);
        } catch (\Exception $e) {
            return new ErrorResponse($e->getMessage(), 422);
        }
    }

    public function edit(Request $request, Response $response, $args): Response
    {
        try {
            /** @var \VOBetting\Bookmaker $bookmakerSer */
            $bookmakerSer = $this->serializer->deserialize($this->getRawData(), 'VOBetting\Bookmaker', 'json');

            $bookmaker = $this->bookmakerRepos->find((int) $args['id']);
            if ($bookmaker === null) {
                throw new \Exception("de bookmaker met het opgegeven id bestaat niet meer", E_ERROR);
            }

            $bookmakerWithSameName = $this->bookmakerRepos->findOneBy(array('name' => $bookmakerSer->getName() ));
            if ($bookmakerWithSameName !== null and $bookmakerWithSameName !== $bookmaker) {
                throw new \Exception("de bookmaker met de naam ".$bookmakerSer->getName()." bestaat al", E_ERROR);
            }
            $bookmaker->setName($bookmakerSer->getName());
            $bookmaker->setExchange($bookmakerSer->getExchange());
            $this->bookmakerRepos->save($bookmaker);

            $json = $this->serializer->serialize($bookmaker, 'json');
            return $this->respondWithJson($response, $json);
        } catch (\Exception $e) {
            return new ErrorResponse($e->getMessage(), 422);
        }
    }

    public function remove(Request $request, Response $response, $args): Response
    {
        try {
            $bookmaker = $this->bookmakerRepos->find((int) $args['id']);
            if ($bookmaker === null) {
                throw new \Exception("geen bookmaker met het opgegeven id gevonden", E_ERROR);
            }
            $this->bookmakerRepos->remove($bookmaker);
            return $response->withStatus(200);
        } catch (\Exception $e) {
            return new ErrorResponse($e->getMessage(), 422);
        }
    }
}
