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
use Sports\Season\Repository as SeasonRepository;
use Sports\Season;

final class SeasonAction extends Action
{
    /**
     * @var SeasonRepository
     */
    private $seasonRepos;
    /**
     * @var Configuration
     */
    protected $config;

    public function __construct(
        LoggerInterface $logger,
        SerializerInterface $serializer,
        SeasonRepository $seasonRepos,
        Configuration $config
    ) {
        parent::__construct($logger, $serializer);
        $this->seasonRepos = $seasonRepos;
        $this->config = $config;
    }

    public function fetch(Request $request, Response $response, $args): Response
    {
        try {
            $seasons = $this->seasonRepos->findAll();

            $json = $this->serializer->serialize($seasons, 'json');
            return $this->respondWithJson($response, $json);
        } catch (\Exception $e) {
            return new ErrorResponse($e->getMessage(), 400);
        }
    }


    public function fetchOne(Request $request, Response $response, $args): Response
    {
        try {
            $season = $this->seasonRepos->find((int) $args['id']);
            if ($season === null) {
                throw new \Exception("geen seizoenen met het opgegeven id gevonden", E_ERROR);
            }
            $json = $this->serializer->serialize($season, 'json');
            return $this->respondWithJson($response, $json);
        } catch (\Exception $e) {
            return new ErrorResponse($e->getMessage(), 400);
        }
    }

    public function add(Request $request, Response $response, $args): Response
    {
        try {
            /** @var \Sports\Season $seasonSer */
            $seasonSer = $this->serializer->deserialize($this->getRawData(), 'Sports\Season', 'json');

            $seasonWithSameName = $this->seasonRepos->findOneBy(array('name' => $seasonSer->getName() ));
            if ($seasonWithSameName !== null) {
                throw new \Exception("het seizoen met de naam ".$seasonSer->getName()." bestaat al", E_ERROR);
            }

            $newSeason = new Season($seasonSer->getName(), $seasonSer->getPeriod());
            // $newSeason->setDescription($seasonSer->getDescription());
            $this->seasonRepos->save($newSeason);

            $json = $this->serializer->serialize($newSeason, 'json');
            return $this->respondWithJson($response, $json);
        } catch (\Exception $e) {
            return new ErrorResponse($e->getMessage(), 422);
        }
    }

    public function edit(Request $request, Response $response, $args): Response
    {
        try {
            /** @var \Sports\Season $seasonSer */
            $seasonSer = $this->serializer->deserialize($this->getRawData(), 'Sports\Season', 'json');

            $season = $this->seasonRepos->find($args['id']);
            if ($season === null) {
                throw new \Exception("het seizoen kon niet gevonden worden o.b.v. de invoer", E_ERROR);
            }

            $seasonWithSameName = $this->seasonRepos->findOneBy(array('name' => $seasonSer->getName() ));
            if ($seasonWithSameName !== null and $seasonWithSameName !== $season) {
                throw new \Exception("het seizoen met de naam ".$seasonSer->getName()." bestaat al", E_ERROR);
            }

            $season->setName($seasonSer->getName());
            $season->setStartDateTime($seasonSer->getStartDateTime());
            $season->setEndDateTime($seasonSer->getEndDateTime());
            $this->seasonRepos->save($season);

            $json = $this->serializer->serialize($season, 'json');
            return $this->respondWithJson($response, $json);
        } catch (\Exception $e) {
            return new ErrorResponse($e->getMessage(), 422);
        }
    }

    public function remove(Request $request, Response $response, $args): Response
    {
        try {
            $season = $this->seasonRepos->find((int) $args['id']);
            if ($season === null) {
                throw new \Exception("geen seizoen met het opgegeven id gevonden", E_ERROR);
            }
            $this->seasonRepos->remove($season);
            return $response->withStatus(200);
        } catch (\Exception $e) {
            return new ErrorResponse($e->getMessage(), 422);
        }
    }
}
