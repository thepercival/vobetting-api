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
use VOBetting\BetGameRepository;
use VOBetting\BetGameFilter;

final class BetGameAction extends Action
{
    /**
     * @var BetGameRepository
     */
    private $betGameRepos;
    /**
     * @var Configuration
     */
    protected $config;

    public function __construct(
        LoggerInterface $logger,
        SerializerInterface $serializer,
        BetGameRepository $betGameRepos,
        Configuration $config
    ) {
        parent::__construct($logger, $serializer);
        $this->betGameRepos = $betGameRepos;
        $this->config = $config;
    }

    public function fetch(Request $request, Response $response, $args): Response
    {
        try {
            /** @var BetGameFilter $betGameFilter */
            $betGameFilter = $this->serializer->deserialize($this->getRawData(), 'VOBetting\BetGameFilter', 'json');

            $betGames = $this->betGameRepos->findByExt( $betGameFilter );

            $json = $this->serializer->serialize($betGames, 'json');
            return $this->respondWithJson($response, $json);
        } catch (\Exception $e) {
            return new ErrorResponse($e->getMessage(), 400);
        }
    }
}
