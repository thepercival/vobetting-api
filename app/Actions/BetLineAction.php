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
use VOBetting\BetLine\Repository as BetLineRepository;
use Voetbal\Game\Repository as GameRepository;

final class BetLineAction extends Action
{
    /**
     * @var BetLineRepository
     */
    private $betLineRepos;
    /**
     * @var GameRepository
     */
    private $gameRepos;
    /**
     * @var Configuration
     */
    protected $config;

    public function __construct(
        LoggerInterface $logger,
        SerializerInterface $serializer,
        BetLineRepository $betLineRepos,
        GameRepository $gameRepos,
        Configuration $config
    ) {
        parent::__construct($logger, $serializer);
        $this->betLineRepos = $betLineRepos;
        $this->gameRepos = $gameRepos;
        $this->config = $config;
    }

    public function fetch(Request $request, Response $response, $args): Response
    {
        try {
            if (array_key_exists("gameId", $args) === false || strlen($args["gameId"]) === 0) {
                throw new \Exception("geen wedstrijdid opgegeven", E_ERROR);
            }

            $game = $this->gameRepos->find((int)$args["gameId"]);
            if ($game === null) {
                throw new \Exception("er kan geen wedstrijd worden gevonden o.b.v. de invoergegevens", E_ERROR);
            }
            $filters = array( "game" => $game );
//            if (array_key_exists("betType", $queryParams) && strlen($queryParams["betType"]) > 0) {
//                $filters["betType"] = (int)$queryParams["betType"];
//            }
            $betLines = $this->betLineRepos->findBy($filters);

            $json = $this->serializer->serialize($betLines, 'json');
            return $this->respondWithJson($response, $json);
        } catch (\Exception $e) {
            return new ErrorResponse($e->getMessage(), 400);
        }
    }

    public function fetchOne(Request $request, Response $response, $args): Response
    {
        try {
            $betLine = $this->betLineRepos->find((int) $args['id']);
            if ($betLine === null) {
                throw new \Exception("geen wedregel met het opgegeven id gevonden", E_ERROR);
            }
            $json = $this->serializer->serialize($betLine, 'json');
            return $this->respondWithJson($response, $json);
        } catch (\Exception $e) {
            return new ErrorResponse($e->getMessage(), 400);
        }
    }
}
