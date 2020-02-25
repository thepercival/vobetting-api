<?php
/**
 * Created by PhpStorm.
 * User: coen
 * Date: 14-11-17
 * Time: 14:04
 */

namespace App\Actions\Voetbal;

use App\Response\ErrorResponse;
use Psr\Log\LoggerInterface;
use JMS\Serializer\SerializerInterface;
use App\Actions\Action;
use Doctrine\ORM\EntityManager;
use JMS\Serializer\Serializer;
use League\Period\Period;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Voetbal\Planning\Repository as PlanningRepository;
use Voetbal\Structure\Repository as StructureRepository;
use Voetbal\Planning\ScheduleService;
use Voetbal\Planning\Input\Repository as InputRepository;
use App\Actions\Voetbal\Deserialize\RefereeService as DeserializeRefereeService;
use Voetbal\Round\Number\PlanningCreator as RoundNumberPlanningCreator;

final class PlanningAction extends Action
{
    /**
     * @var PlanningRepository
     */
    protected $repos;
    /**
     * @var InputRepository
     */
    protected $inputRepos;
    /**
     * @var StructureRepository
     */
    protected $structureRepos;
    /**
     * @var DeserializeRefereeService
     */
    protected $deserializeRefereeService;

    public function __construct(
        LoggerInterface $logger,
        SerializerInterface $serializer,
        PlanningRepository $repos,
        InputRepository $inputRepos,
        StructureRepository $structureRepos
    ) {
        parent::__construct($logger,$serializer);

        $this->repos = $repos;
        $this->inputRepos = $inputRepos;
        $this->structureRepos = $structureRepos;
        $this->serializer = $serializer;
        $this->deserializeRefereeService = new DeserializeRefereeService();
    }

    public function fetch( Request $request, Response $response, $args ): Response
    {
        list($structure, $roundNumber) = $this->getFromRequest($request, $args);
        $json = $this->serializer->serialize($structure, 'json');
        return $this->respondWithJson($response, $json);
    }

    /**
     * do game remove and add for multiple games
     *
     */
    public function create( Request $request, Response $response, $args ): Response
    {
        /** @var \FCToernooi\Tournament $tournament */
        $tournament = $request->getAttribute("tournament");

        try {
            list($structure, $roundNumber) = $this->getFromRequest($request, $args);

            $roundNumberPlanningCreator = new RoundNumberPlanningCreator($this->inputRepos, $this->repos);

            $roundNumberPlanningCreator->create($roundNumber, $tournament->getBreak());

            $json = $this->serializer->serialize($structure, 'json');
            return $this->respondWithJson($response, $json);
        } catch (\Exception $e) {
            return new ErrorResponse($e->getMessage(), 422);
        }
    }



    /**
     * do game remove and add for multiple games
     */
    public function reschedule( Request $request, Response $response, $args ): Response
    {
        /** @var \FCToernooi\Tournament $tournament */
        $tournament = $request->getAttribute("tournament");

        try {
            list($structure, $roundNumber) = $this->getFromRequest($request, $args);
            $scheduleService = new ScheduleService($tournament->getBreak());
            $dates = $scheduleService->rescheduleGames($roundNumber);

            $this->repos->saveRoundNumber($roundNumber);

            $json = $this->serializer->serialize($dates, 'json');
            return $this->respondWithJson($response, $json);
        } catch (\Exception $e) {
            return new ErrorResponse($e->getMessage(), 422);
        }
    }

    protected function getFromRequest( Request $request, $args ): array {
        $competition = $request->getAttribute("tournament")->getCompetition();
        if( array_key_exists("roundnumber", $args ) === false ) {
            throw new \Exception("geen rondenummer opgegeven", E_ERROR);
        }
        /** @var \Voetbal\Structure $structure */
        $structure = $this->structureRepos->getStructure( $competition );
        $roundNumber = $structure->getRoundNumber( $args["roundnumber"] );
        return [$structure, $roundNumber];
    }
}