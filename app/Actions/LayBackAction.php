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
use VOBetting\LayBack\Repository as LayBackRepository;
use Voetbal\Game\Repository as GameRepository;

final class LayBackAction extends Action
{
    /**
     * @var LayBackRepository
     */
    private $layBackRepos;
    /**
     * @var BetLineRepository
     */
    protected $betLineRepos;
    /**
     * @var Configuration
     */
    protected $config;

    public function __construct(
        LoggerInterface $logger,
        SerializerInterface $serializer,
        LayBackRepository $layBackRepos,
        BetLineRepository $betLineRepos,
        Configuration $config
    ) {
        parent::__construct($logger, $serializer);
        $this->layBackRepos = $layBackRepos;
        $this->betLineRepos = $betLineRepos;
        $this->config = $config;
    }

    public function fetch(Request $request, Response $response, $args): Response
    {
        try {
            $queryParams = $request->getQueryParams();

            if (array_key_exists("betlineid", $queryParams) === false || strlen($queryParams["betlineid"]) === 0) {
                throw new \Exception("geen wedregelid opgegeven", E_ERROR);
            }

            $betLine = $this->betLineRepos->find((int)$queryParams["betlineid"]);
            if ($betLine === null) {
                throw new \Exception("er kan geen wedregel worden gevonden o.b.v. de invoergegevens", E_ERROR);
            }
            $layBacks = $this->layBackRepos->findBy(array( "betLine" => $betLine ));

            $json = $this->serializer->serialize($layBacks, 'json');
            return $this->respondWithJson($response, $json);
        } catch (\Exception $e) {
            return new ErrorResponse($e->getMessage(), 400);
        }
    }
    
    public function fetchOne(Request $request, Response $response, $args): Response
    {
        try {
            $layBack = $this->layBackRepos->find((int) $args['id']);
            if ($layBack === null) {
                throw new \Exception("geen layback met het opgegeven id gevonden", E_ERROR);
            }
            $json = $this->serializer->serialize($layBack, 'json');
            return $this->respondWithJson($response, $json);
        } catch (\Exception $e) {
            return new ErrorResponse($e->getMessage(), 400);
        }
    }
}
