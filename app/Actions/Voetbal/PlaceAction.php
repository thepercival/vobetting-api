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
use JMS\Serializer\SerializerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Voetbal\Competitor\Repository as CompetitorRepos;
use Voetbal\Place\Repository as PlaceRepository;
use Voetbal\Poule;
use Voetbal\Poule\Repository as PouleRepository;
use App\Actions\Action;
use Voetbal\Place;
use Voetbal\Competition;

final class PlaceAction extends Action
{
    /**
     * @var PlaceRepository
     */
    protected $placeRepos;
    /**
     * @var PouleRepository
     */
    protected $pouleRepos;
    /**
     * @var CompetitorRepos
     */
    protected $competitorRepos;

    public function __construct(
        LoggerInterface $logger,
        SerializerInterface $serializer,
        PlaceRepository $placeRepos,
        PouleRepository $pouleRepos,
        CompetitorRepos $competitorRepos
    ) {
        parent::__construct($logger, $serializer);
        $this->placeRepos = $placeRepos;
        $this->pouleRepos = $pouleRepos;
        $this->competitorRepos = $competitorRepos;
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
            /** @var \Voetbal\Place $placeSer */
            $placeSer = $this->serializer->deserialize($this->getRawData(), 'Voetbal\Place', 'json');

            $place = $this->placeRepos->find((int)$args["placeId"]);
            if ($place === null) {
                throw new \Exception("de pouleplek kan niet gevonden worden o.b.v. id", E_ERROR);
            }
            if ($place->getPoule() !== $poule) {
                throw new \Exception("de poule van de pouleplek komt niet overeen met de verstuurde poule", E_ERROR);
            }
            $competitor = $placeSer->getCompetitor() ? $this->competitorRepos->find(
                $placeSer->getCompetitor()->getId()
            ) : null;
            $place->setCompetitor($competitor);

            $this->placeRepos->save($place);

            $json = $this->serializer->serialize($place, 'json');
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