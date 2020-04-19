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
use Voetbal\Association\Repository as AssociationRepository;
use Voetbal\Association;

final class AssociationAction extends Action
{
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
        AssociationRepository $associationRepos,
        Configuration $config
    ) {
        parent::__construct($logger, $serializer);
        $this->associationRepos = $associationRepos;
        $this->config = $config;
    }

    public function fetch(Request $request, Response $response, $args): Response
    {
        try {
            $associations = $this->associationRepos->findAll();

            $json = $this->serializer->serialize($associations, 'json');
            return $this->respondWithJson($response, $json);
        } catch (\Exception $e) {
            return new ErrorResponse($e->getMessage(), 400);
        }
    }


    public function fetchOne(Request $request, Response $response, $args): Response
    {
        try {
            $association = $this->associationRepos->find((int) $args['id']);
            if ($association === null) {
                throw new \Exception("geen bonden met het opgegeven id gevonden", E_ERROR);
            }
            $json = $this->serializer->serialize($association, 'json');
            return $this->respondWithJson($response, $json);
        } catch (\Exception $e) {
            return new ErrorResponse($e->getMessage(), 400);
        }
    }

    public function add(Request $request, Response $response, $args): Response
    {
        try {
            /** @var \Voetbal\Association $associationSer */
            $associationSer = $this->serializer->deserialize($this->getRawData(), 'Voetbal\Association', 'json');

            $associationWithSameName = $this->associationRepos->findOneBy(array('name' => $associationSer->getName() ));
            if ($associationWithSameName !== null) {
                throw new \Exception("de bond met de naam ".$associationSer->getName()." bestaat al", E_ERROR);
            }

            $parentAssociation = null;
            if ($associationSer->getParent() !== null) {
                $parentAssociation = $this->associationRepos->findOneBy(["name" => $associationSer->getParent()->getName()]);
            }

            $newAssociation = new Association($associationSer->getName());
            $newAssociation->setDescription($associationSer->getDescription());
            $associationService = new Association\Service();
            $newAssociation = $associationService->changeParent($newAssociation, $parentAssociation);
            $this->associationRepos->save($newAssociation);

            $json = $this->serializer->serialize($newAssociation, 'json');
            return $this->respondWithJson($response, $json);
        } catch (\Exception $e) {
            return new ErrorResponse($e->getMessage(), 422);
        }
    }

    public function edit(Request $request, Response $response, $args): Response
    {
        try {
            /** @var \Voetbal\Association $associationSer */
            $associationSer = $this->serializer->deserialize($this->getRawData(), 'Voetbal\Association', 'json');

            $association = $this->associationRepos->find($args['id']);
            if ($association === null) {
                throw new \Exception("de bond kon niet gevonden worden o.b.v. de invoer", E_ERROR);
            }
            $parentAssociation = null;
            if ($associationSer->getParent() !== null) {
                $parentAssociation = $this->associationRepos->findOneBy(["name" => $associationSer->getParent()->getName()]);
            }

            $associationWithSameName = $this->associationRepos->findOneBy(array('name' => $associationSer->getName() ));
            if ($associationWithSameName !== null and $associationWithSameName !== $association) {
                throw new \Exception("de bond met de naam ".$associationSer->getName()." bestaat al", E_ERROR);
            }

            $association->setName($associationSer->getName());
            $association->setDescription($associationSer->getDescription());
            $associationService = new Association\Service();
            $association = $associationService->changeParent($association, $parentAssociation);
            $this->associationRepos->save($association);

            $json = $this->serializer->serialize($association, 'json');
            return $this->respondWithJson($response, $json);
        } catch (\Exception $e) {
            return new ErrorResponse($e->getMessage(), 422);
        }
    }

    public function remove(Request $request, Response $response, $args): Response
    {
        try {
            $association = $this->associationRepos->find((int) $args['id']);
            if ($association === null) {
                throw new \Exception("geen bond met het opgegeven id gevonden", E_ERROR);
            }
            $this->associationRepos->remove($association);
            return $response->withStatus(200);
        } catch (\Exception $e) {
            return new ErrorResponse($e->getMessage(), 422);
        }
    }
}
