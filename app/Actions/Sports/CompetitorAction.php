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
use Sports\Competitor;
use Sports\Competitor\Repository as CompetitorRepository;
use Sports\Association\Repository as AssociationRepository;
use Sports\Association;
use Sports\League;

final class CompetitorAction extends Action
{
    /**
     * @var CompetitorRepository
     */
    private $competitorRepos;
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
        CompetitorRepository $competitorRepos,
        AssociationRepository $associationRepos,
        Configuration $config
    ) {
        parent::__construct($logger, $serializer);
        $this->competitorRepos = $competitorRepos;
        $this->associationRepos = $associationRepos;
        $this->config = $config;
    }

    public function fetch(Request $request, Response $response, $args): Response
    {
        try {
            $filter = [];
            {
                $queryParams = $request->getQueryParams();
                if (array_key_exists("associationId", $queryParams) && strlen($queryParams["associationId"]) > 0) {
                    $association = $this->associationRepos->find($queryParams["associationId"]);
                    $filter["association"] = $association;
                }
            }
            $competitors = $this->competitorRepos->findBy($filter);

            $json = $this->serializer->serialize($competitors, 'json');
            return $this->respondWithJson($response, $json);
        } catch (\Exception $e) {
            return new ErrorResponse($e->getMessage(), 400);
        }
    }

    public function fetchOne(Request $request, Response $response, $args): Response
    {
        try {
            $competitor = $this->competitorRepos->find((int) $args['id']);
            if ($competitor === null) {
                throw new \Exception("geen deelnemer met het opgegeven id gevonden", E_ERROR);
            }
            $json = $this->serializer->serialize($competitor, 'json');
            return $this->respondWithJson($response, $json);
        } catch (\Exception $e) {
            return new ErrorResponse($e->getMessage(), 400);
        }
    }

    public function add(Request $request, Response $response, $args): Response
    {
        try {
            /** @var Competitor $competitorSer */
            $competitorSer = $this->serializer->deserialize($this->getRawData(), 'Sports\Competitor', 'json');

            $association = null;
            $queryParams = $request->getQueryParams();
            if (array_key_exists("associationId", $queryParams) && strlen($queryParams["associationId"]) > 0) {
                $association = $this->associationRepos->find($queryParams["associationId"]);
            }
            if ($association === null) {
                throw new \Exception("de bond voor de deelnemer kon niet gevonden worden o.b.v. de invoer", E_ERROR);
            }

            $competitorWithSameName = $this->competitorRepos->findOneBy(array('name' => $competitorSer->getName() ));
            if ($competitorWithSameName !== null) {
                throw new \Exception("de deelnemer met de naam ".$competitorSer->getName()." bestaat al", E_ERROR);
            }

            $newCompetitor = new Competitor($association, $competitorSer->getName());
            $newCompetitor->setAbbreviation($competitorSer->getAbbreviation());
            $newCompetitor->setImageUrl($competitorSer->getImageUrl());
            $this->competitorRepos->save($newCompetitor);

            $json = $this->serializer->serialize($newCompetitor, 'json');
            return $this->respondWithJson($response, $json);
        } catch (\Exception $e) {
            return new ErrorResponse($e->getMessage(), 422);
        }
    }

    public function edit(Request $request, Response $response, $args): Response
    {
        try {
            /** @var Competitor $competitorSer */
            $competitorSer = $this->serializer->deserialize($this->getRawData(), 'Sports\Competitor', 'json');

            $competitor = $this->competitorRepos->find($args['id']);
            if ($competitor === null) {
                throw new \Exception("de deelnemer kon niet gevonden worden o.b.v. de invoer", E_ERROR);
            }

            $competitorWithSameName = $this->competitorRepos->findOneBy(array('name' => $competitorSer->getName() ));
            if ($competitorWithSameName !== null and $competitorWithSameName !== $competitor) {
                throw new \Exception("de deelnemer met de naam ".$competitorSer->getName()." bestaat al", E_ERROR);
            }

            $competitor->setName($competitorSer->getName());
            $competitor->setAbbreviation($competitorSer->getAbbreviation());
            $competitor->setImageUrl($competitorSer->getImageUrl());
            $this->competitorRepos->save($competitor);

            $json = $this->serializer->serialize($competitor, 'json');
            return $this->respondWithJson($response, $json);
        } catch (\Exception $e) {
            return new ErrorResponse($e->getMessage(), 422);
        }
    }

    public function remove(Request $request, Response $response, $args): Response
    {
        try {
            $competitor = $this->competitorRepos->find((int) $args['id']);
            if ($competitor === null) {
                throw new \Exception("geen deelnemer met het opgegeven id gevonden", E_ERROR);
            }
            $this->competitorRepos->remove($competitor);
            return $response->withStatus(200);
        } catch (\Exception $e) {
            return new ErrorResponse($e->getMessage(), 422);
        }
    }
}
