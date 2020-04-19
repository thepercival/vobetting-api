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
use Voetbal\Competition\Repository as CompetitionRepository;
use Voetbal\Structure\Repository as StructureRepository;

final class StructureAction extends Action
{
    /**
     * @var StructureRepository
     */
    protected $structureRepos;
    /**
     * @var CompetitionRepository
     */
    protected $competitionRepos;
    /**
     * @var Configuration
     */
    protected $config;

    public function __construct(
        LoggerInterface $logger,
        SerializerInterface $serializer,
        StructureRepository $structureRepos,
        CompetitionRepository $competitionRepos,
        Configuration $config
    ) {
        parent::__construct($logger, $serializer);
        $this->structureRepos = $structureRepos;
        $this->competitionRepos = $competitionRepos;
        $this->config = $config;
    }

    public function fetchOne(Request $request, Response $response, $args): Response
    {
        try {
            $competition = $this->competitionRepos->find((int) $args['id']);
            if ($competition === null) {
                throw new \Exception("geen competitieseizoen met het opgegeven id gevonden", E_ERROR);
            }
            $structure = $this->structureRepos->getStructure($competition);
            if ($structure === null) {
                throw new \Exception("geen structuur gevonden bij het competitieseizoen", E_ERROR);
            }
            // var_dump($structure); die();

            $json = $this->serializer->serialize($structure, 'json');
            return $this->respondWithJson($response, $json);
        } catch (\Exception $e) {
            return new ErrorResponse($e->getMessage(), 400);
        }
    }
}
