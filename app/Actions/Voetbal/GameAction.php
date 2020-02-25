<?php
/**
 * Created by PhpStorm.
 * User: coen
 * Date: 14-11-17
 * Time: 14:02
 */

namespace App\Actions\Voetbal;

use App\Response\ErrorResponse;
use Psr\Log\LoggerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use JMS\Serializer\SerializerInterface;
use Voetbal\Game\Score\Repository as GameScoreRepository;
use Voetbal\Game\Repository as GameRepository;
use Voetbal\Poule;
use Voetbal\Poule\Repository as PouleRepository;
use App\Actions\Action;
use Voetbal\Competition;
use Voetbal\Game\Service as GameService;

final class GameAction extends Action
{
    /**
     * @var GameRepository
     */
    protected $gameRepos;
    /**
     * @var PouleRepository
     */
    protected $pouleRepos;
    /**
     * @var GameScoreRepository
     */
    protected $gameScoreRepos;

    public function __construct(
        LoggerInterface $logger,
        SerializerInterface $serializer,
        GameRepository $gameRepos,
        PouleRepository $pouleRepos,
        GameScoreRepository $gameScoreRepos
    ) {
        parent::__construct($logger, $serializer);
        $this->gameRepos = $gameRepos;
        $this->pouleRepos = $pouleRepos;
        $this->gameScoreRepos = $gameScoreRepos;
    }

    public function edit(Request $request, Response $response, $args): Response
    {
        try {
            /** @var \Voetbal\Competition $competition */
            $competition = $request->getAttribute("tournament")->getCompetition();

            $queryParams = $request->getQueryParams();
            $pouleId = 0;
            if (array_key_exists("pouleId", $queryParams) && strlen($queryParams["pouleId"]) > 0) {
                $pouleId = (int)$queryParams["pouleId"];
            }
            $poule = $this->getPouleFromInput($pouleId, $competition);

            /** @var \Voetbal\Game $gameSer */
            $gameSer = $this->serializer->deserialize($this->getRawData(), 'Voetbal\Game', 'json');

            $game = $this->gameRepos->find((int)$args["gameId"]);
            if ($game === null) {
                throw new \Exception("de pouleplek kan niet gevonden worden o.b.v. id", E_ERROR);
            }
            if ($game->getPoule() !== $poule) {
                throw new \Exception("de poule van de pouleplek komt niet overeen met de verstuurde poule", E_ERROR);
            }

            $this->gameScoreRepos->removeScores($game);

            $game->setState($gameSer->getState());
            $game->setStartDateTime($gameSer->getStartDateTime());
            $gameService = new GameService();
            $gameService->addScores($game, $gameSer->getScores()->toArray());

            $this->gameRepos->save($game);

            $json = $this->serializer->serialize($game, 'json');
            return $this->respondWithJson($response, $json);
        } catch (\Exception $e) {
            return new ErrorResponse($e->getMessage(), 422);
        }
    }

    protected function getPouleFromInput(int $pouleId, Competition $competition): Poule
    {
        $poule = $this->pouleRepos->find($pouleId);
        if ($poule === null) {
            throw new \Exception("er kan poule worden gevonden o.b.v. de invoergegevens", E_ERROR);
        }
        if ($poule->getRound()->getNumber()->getCompetition() !== $competition) {
            throw new \Exception("de competitie van de poule komt niet overeen met de verstuurde competitie", E_ERROR);
        }
        return $poule;
    }
}