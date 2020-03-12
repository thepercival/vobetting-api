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
use Voetbal\Competition;
use Voetbal\Competition\Repository as CompetitionRepository;
use Voetbal\League\Repository as LeagueRepository;
use Voetbal\League;
use Voetbal\Season\Repository as SeasonRepository;
use Voetbal\Season;

final class CompetitionAction extends Action
{
    /**
     * @var CompetitionRepository
     */
    private $competitionRepos;
    /**
     * @var LeagueRepository
     */
    private $leagueRepos;
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
        CompetitionRepository $competitionRepos,
        LeagueRepository $leagueRepos,
        SeasonRepository $seasonRepos,
        Configuration $config
    )
    {
        parent::__construct($logger,$serializer);
        $this->competitionRepos = $competitionRepos;
        $this->leagueRepos = $leagueRepos;
        $this->seasonRepos = $seasonRepos;
        $this->config = $config;
    }

    public function fetch( Request $request, Response $response, $args ): Response
    {
        try {
            $filter = [];
            {
                $queryParams = $request->getQueryParams();
                if (array_key_exists("leagueId", $queryParams) && strlen($queryParams["leagueId"]) > 0) {
                    $league = $this->leagueRepos->find( $queryParams["leagueId"] );
                    $filter["league"] = $league;
                }
                if (array_key_exists("seasonId", $queryParams) && strlen($queryParams["seasonId"]) > 0) {
                    $season = $this->seasonRepos->find( $queryParams["seasonId"] );
                    $filter["season"] = $season;
                }
            }
            $competitions = $this->competitionRepos->findBy( $filter );

            $json = $this->serializer->serialize( $competitions, 'json');
            return $this->respondWithJson( $response, $json );
        }
        catch( \Exception $e ){
            return new ErrorResponse($e->getMessage(), 400);
        }
    }

    public function fetchOne( Request $request, Response $response, $args ): Response
    {
        try {
            $competition = $this->competitionRepos->find((int) $args['id']);
            if ( $competition === null ) {
                throw new \Exception("geen competitieseizoen met het opgegeven id gevonden", E_ERROR);
            }
            $json = $this->serializer->serialize( $competition, 'json');
            return $this->respondWithJson( $response, $json );
        }
        catch( \Exception $e ){
            return new ErrorResponse($e->getMessage(), 400);
        }
    }

    public function add( Request $request, Response $response, $args ): Response
    {
        try {
            /** @var \Voetbal\Competition $competitionSer */
            $competitionSer = $this->serializer->deserialize($this->getRawData(), 'Voetbal\Competition', 'json');

            $queryParams = $request->getQueryParams();
            $leagueId = null;
            if (array_key_exists("leagueId", $queryParams) && strlen($queryParams["leagueId"]) > 0) {
                $leagueId = $queryParams["leagueId"];
            }
            $league = $this->leagueRepos->find( $leagueId );
            if ( $league === null ) {
                throw new \Exception("de competitie kon niet gevonden worden o.b.v. de invoer", E_ERROR);
            }
            $seasonId = null;
            if (array_key_exists("seasonId", $queryParams) && strlen($queryParams["seasonId"]) > 0) {
                $seasonId = $queryParams["seasonId"];
            }
            $season = $this->seasonRepos->find( $seasonId );
            if ( $season === null ) {
                throw new \Exception("het seizoen kon niet gevonden worden o.b.v. de invoer", E_ERROR);
            }

            $existingCompetition = $this->competitionRepos->findOneBy(
                array(
                    'league' => $league,
                    'season' => $season
                )
            );
            if ( $existingCompetition !== null ){
                throw new \Exception("het competitieseizoen voor de competitie en seizoen bestaat al", E_ERROR );
            }

            $newCompetition = new Competition( $league, $season );
            $newCompetition->setStartDateTime( $competitionSer->getStartDateTime() );

            $this->competitionRepos->save( $newCompetition );

            $json = $this->serializer->serialize( $newCompetition, 'json');
            return $this->respondWithJson( $response, $json );
        }
        catch( \Exception $e ){
            return new ErrorResponse($e->getMessage(), 422);
        }
    }

    public function edit( Request $request, Response $response, $args ): Response
    {
        try {
            /** @var \Voetbal\Competition $competitionSer */
            $competitionSer = $this->serializer->deserialize($this->getRawData(), 'Voetbal\Competition', 'json');

            $competition = $this->competitionRepos->find($args['id']);
            if ( $competition === null ) {
                throw new \Exception("het competitieseizoen kon niet gevonden worden o.b.v. de invoer", E_ERROR);
            }

            $competition->setStartDateTime( $competitionSer->getStartDateTime() );
            $this->competitionRepos->save( $competition );

            $json = $this->serializer->serialize( $competition, 'json');
            return $this->respondWithJson( $response, $json );
        }
        catch( \Exception $e ){
            return new ErrorResponse($e->getMessage(), 422);
        }
    }

    public function remove( Request $request, Response $response, $args ): Response
    {
        try{
            $competition = $this->competitionRepos->find((int) $args['id']);
            if ( $competition === null ) {
                throw new \Exception("geen competitieseizoen met het opgegeven id gevonden", E_ERROR);
            }
            $this->competitionRepos->remove( $competition );
            return $response->withStatus(200);
        }
        catch( \Exception $e ){
            return new ErrorResponse( $e->getMessage(), 422);
        }
    }
}