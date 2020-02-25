<?php
/**
 * Created by PhpStorm.
 * User: coen
 * Date: 14-11-17
 * Time: 14:02
 */

namespace App\Actions\Voetbal;

use App\Copiers\StructureCopier;
use App\Response\ErrorResponse;
use Psr\Log\LoggerInterface;
use JMS\Serializer\SerializerInterface;
use Voetbal\Structure\Repository as StructureRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Actions\Action;

final class StructureAction extends Action
{
    /**
     * @var StructureRepository
     */
    protected $structureRepos;

    public function __construct(
        LoggerInterface $logger,
        SerializerInterface $serializer,
        StructureRepository $structureRepos
    )
    {
        parent::__construct($logger,$serializer);
        $this->structureRepos = $structureRepos;
    }

    public function fetchOne( Request $request, Response $response, $args ): Response
    {
        $competition = $request->getAttribute("tournament")->getCompetition();

        $structure = $this->structureRepos->getStructure( $competition );
        // var_dump($structure); die();

        $json = $this->serializer->serialize( $structure, 'json');
        return $this->respondWithJson($response, $json);
    }

    public function edit( Request $request, Response $response, $args ): Response
    {
        try {
            /** @var \Voetbal\Structure|false $structureSer */
            $structureSer = $this->serializer->deserialize($this->getRawData(), 'Voetbal\Structure', 'json');
            if ($structureSer === false) {
                throw new \Exception("er kan geen ronde worden gewijzigd o.b.v. de invoergegevens", E_ERROR);
            }
            /** @var \Voetbal\Competition $competition */
            $competition = $request->getAttribute("tournament")->getCompetition();

            $structure = $this->structureRepos->getStructure($competition);
            $competitors = $structure ? $structure->getFirstRoundNumber()->getCompetitors() : [];
            $structureCopier = new StructureCopier($competition, $competitors);
            $newStructure = $structureCopier->copy($structureSer);

            $roundNumberAsValue = 1;
            $this->structureRepos->removeAndAdd($competition, $newStructure, $roundNumberAsValue);

            $json = $this->serializer->serialize($newStructure, 'json');
            return $this->respondWithJson($response, $json);
        }
        catch( \Exception $e ){
            return new ErrorResponse($e->getMessage(), 422);
        }
    }
}