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
use App\Actions\Action;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Log\LoggerInterface;
use JMS\Serializer\SerializerInterface;
use Voetbal\ExternalSource;
use Voetbal\ExternalSource\Repository as ExternalSourceRepository;
use Voetbal\Attacher\Repository as AttacherRepos;
use Voetbal\Sport\Repository as SportRepository;
use Voetbal\Attacher\Sport\Repository as SportAttacherRepository;
use Voetbal\Association\Repository as AssociationRepository;
use Voetbal\Attacher\Association\Repository as AssociationAttacherRepository;
use Voetbal\Season\Repository as SeasonRepository;
use Voetbal\Attacher\Season\Repository as SeasonAttacherRepository;
use Voetbal\League\Repository as LeagueRepository;
use Voetbal\Attacher\League\Repository as LeagueAttacherRepository;
use Voetbal\Competition\Repository as CompetitionRepository;
use Voetbal\Attacher\Competition\Repository as CompetitionAttacherRepository;
use Voetbal\Competitor\Repository as CompetitorRepository;
use Voetbal\Attacher\Competitor\Repository as CompetitorAttacherRepository;
use Voetbal\Repository as VoetbalRepository;
use Voetbal\Attacher\Factory as AttacherFactory;

final class AttacherAction extends Action
{
    /**
     * @var ExternalSourceRepository
     */
    private $externalSourceRepos;
    /**
     * @var SportRepository
     */
    private $sportRepos;
    /**
     * @var SportAttacherRepository
     */
    private $sportAttacherRepos;
    /**
     * @var AssociationAttacherRepository
     */
    private $associationAttacherRepos;
    /**
     * @var AssociationRepository
     */
    private $associationRepos;
    /**
     * @var SeasonAttacherRepository
     */
    private $seasonAttacherRepos;
    /**
     * @var SeasonRepository
     */
    private $seasonRepos;
    /**
     * @var LeagueAttacherRepository
     */
    private $leagueAttacherRepos;
    /**
     * @var LeagueRepository
     */
    private $leagueRepos;
    /**
     * @var CompetitionAttacherRepository
     */
    private $competitionAttacherRepos;
    /**
     * @var CompetitionRepository
     */
    private $competitionRepos;
    /**
     * @var CompetitorAttacherRepository
     */
    private $competitorAttacherRepos;
    /**
     * @var CompetitorRepository
     */
    private $competitorRepos;
    /**
     * @var AttacherFactory
     */
    private $attacherFactory;

    public function __construct(
        LoggerInterface $logger,
        SerializerInterface $serializer,
        ExternalSourceRepository $externalSourceRepos,
        SportAttacherRepository $sportAttacherRepos,
        SportRepository $sportRepos,
        AssociationAttacherRepository $associationAttacherRepos,
        AssociationRepository $associationRepos,
        SeasonAttacherRepository $seasonAttacherRepos,
        SeasonRepository $seasonRepos,
        LeagueAttacherRepository $leagueAttacherRepos,
        LeagueRepository $leagueRepos,
        CompetitionAttacherRepository $competitionAttacherRepos,
        CompetitionRepository $competitionRepos,
        CompetitorAttacherRepository $competitorAttacherRepos,
        CompetitorRepository $competitorRepos
    ) {
        parent::__construct($logger, $serializer);
        $this->externalSourceRepos = $externalSourceRepos;
        $this->sportAttacherRepos = $sportAttacherRepos;
        $this->sportRepos = $sportRepos;
        $this->associationAttacherRepos = $associationAttacherRepos;
        $this->associationRepos = $associationRepos;
        $this->seasonAttacherRepos = $seasonAttacherRepos;
        $this->seasonRepos = $seasonRepos;
        $this->leagueAttacherRepos = $leagueAttacherRepos;
        $this->leagueRepos = $leagueRepos;
        $this->competitionAttacherRepos = $competitionAttacherRepos;
        $this->competitionRepos = $competitionRepos;
        $this->competitorAttacherRepos = $competitorAttacherRepos;
        $this->competitorRepos = $competitorRepos;
        $this->attacherFactory = new AttacherFactory();
    }

    public function fetchSports(Request $request, Response $response, $args): Response
    {
        return $this->fetch($this->sportAttacherRepos, $request, $response, $args);
    }

    public function fetchAssociations(Request $request, Response $response, $args): Response
    {
        return $this->fetch($this->associationAttacherRepos, $request, $response, $args);
    }

    public function fetchSeasons(Request $request, Response $response, $args): Response
    {
        return $this->fetch($this->seasonAttacherRepos, $request, $response, $args);
    }

    public function fetchLeagues(Request $request, Response $response, $args): Response
    {
        return $this->fetch($this->leagueAttacherRepos, $request, $response, $args);
    }

    public function fetchCompetitions(Request $request, Response $response, $args): Response
    {
        return $this->fetch($this->competitionAttacherRepos, $request, $response, $args);
    }

    public function fetchCompetition(Request $request, Response $response, $args): Response
    {
        return $this->fetchOne($this->competitionAttacherRepos, $request, $response, $args);
    }

    public function fetchCompetitors(Request $request, Response $response, $args): Response
    {
        return $this->fetch($this->competitorAttacherRepos, $request, $response, $args);
    }

    protected function fetch(AttacherRepos $attacherRepos, Request $request, Response $response, $args): Response
    {
        try {
            $externalSource = $this->externalSourceRepos->find((int)$args['externalSourceId']);
            if ($externalSource === null) {
                throw new \Exception("er is geen externe bron meegegeven", E_ERROR);
            }
            $attachers = $attacherRepos->findBy(["externalSource" => $externalSource]);

            $json = $this->serializer->serialize($attachers, 'json');
            return $this->respondWithJson($response, $json);
        } catch (\Exception $e) {
            return new ErrorResponse($e->getMessage(), 400);
        }
    }

    protected function fetchOne(AttacherRepos $attacherRepos, Request $request, Response $response, $args): Response
    {
        try {
            $externalSource = $this->externalSourceRepos->find((int)$args['externalSourceId']);
            if ($externalSource === null) {
                throw new \Exception("er is geen externe bron meegegeven", E_ERROR);
            }
            $attacher = $attacherRepos->findOneBy([
                "externalSource" => $externalSource, "importable" => (int)$args['importableId']
                ]);
            if ($attacher === null) {
                throw new \Exception("het gekoppelde item kon niet gevonden worden", E_ERROR);
            }

            $json = $this->serializer->serialize($attacher, 'json');
            return $this->respondWithJson($response, $json);
        } catch (\Exception $e) {
            return new ErrorResponse($e->getMessage(), 400);
        }
    }


//    public function fetchOne( Request $request, Response $response, $args ): Response
//    {
//        try {
//            $association = $this->associationRepos->find((int) $args['id']);
//            if ( $association === null ) {
//                throw new \Exception("geen bonden met het opgegeven id gevonden", E_ERROR);
//            }
//            $json = $this->serializer->serialize( $association, 'json');
//            return $this->respondWithJson( $response, $json );
//        }
//        catch( \Exception $e ){
//            return new ErrorResponse($e->getMessage(), 400);
//        }
//    }
//

    public function addSport(Request $request, Response $response, $args): Response
    {
        return $this->add($this->sportRepos, $this->sportAttacherRepos, $request, $response, $args);
    }

    public function addAssociation(Request $request, Response $response, $args): Response
    {
        return $this->add($this->associationRepos, $this->associationAttacherRepos, $request, $response, $args);
    }

    public function addSeason(Request $request, Response $response, $args): Response
    {
        return $this->add($this->seasonRepos, $this->seasonAttacherRepos, $request, $response, $args);
    }

    public function addLeague(Request $request, Response $response, $args): Response
    {
        return $this->add($this->leagueRepos, $this->leagueAttacherRepos, $request, $response, $args);
    }

    public function addCompetition(Request $request, Response $response, $args): Response
    {
        return $this->add($this->competitionRepos, $this->competitionAttacherRepos, $request, $response, $args);
    }

    public function addCompetitor(Request $request, Response $response, $args): Response
    {
        return $this->add($this->competitorRepos, $this->competitorAttacherRepos, $request, $response, $args);
    }

    protected function add(
        VoetbalRepository $importableRepos,
        AttacherRepos $attacherRepos,
        Request $request,
        Response $response,
        $args
    ): Response {
        try {
            $externalSource = $this->externalSourceRepos->find((int)$args['externalSourceId']);
            if ($externalSource === null) {
                throw new \Exception("er is geen externe bron meegegeven", E_ERROR);
            }

            /** @var \Voetbal\Attacher $attacherSer */
            $attacherSer = $this->serializer->deserialize($this->getRawData(), 'Voetbal\Attacher', 'json');

            $importable = $importableRepos->find($attacherSer->getImportableIdForSer());
            if ($importable === null) {
                throw new \Exception("er kan geen importable worden gevonden", E_ERROR);
            }
            $newAttacher = $this->attacherFactory->createObject(
                $importable,
                $externalSource,
                $attacherSer->getExternalId()
            );
            $attacherRepos->save($newAttacher);

            $json = $this->serializer->serialize($newAttacher, 'json');
            return $this->respondWithJson($response, $json);
        } catch (\Exception $e) {
            return new ErrorResponse($e->getMessage(), 422);
        }
    }

    public function removeSport(Request $request, Response $response, $args): Response
    {
        return $this->remove($this->sportAttacherRepos, $request, $response, $args);
    }

    public function removeAssociation(Request $request, Response $response, $args): Response
    {
        return $this->remove($this->associationAttacherRepos, $request, $response, $args);
    }

    public function removeSeason(Request $request, Response $response, $args): Response
    {
        return $this->remove($this->seasonAttacherRepos, $request, $response, $args);
    }

    public function removeLeague(Request $request, Response $response, $args): Response
    {
        return $this->remove($this->leagueAttacherRepos, $request, $response, $args);
    }

    public function removeCompetition(Request $request, Response $response, $args): Response
    {
        return $this->remove($this->competitionAttacherRepos, $request, $response, $args);
    }

    public function removeCompetitor(Request $request, Response $response, $args): Response
    {
        return $this->remove($this->competitorAttacherRepos, $request, $response, $args);
    }

    protected function remove(AttacherRepos $attacherRepos, Request $request, Response $response, $args): Response
    {
        try {
            $attacher = $attacherRepos->find((int)$args['id']);
            if ($attacher === null) {
                throw new \Exception("geen koppeling met het opgegeven id gevonden", E_ERROR);
            }
            $attacherRepos->remove($attacher);
            return $response->withStatus(200);
        } catch (\Exception $e) {
            return new ErrorResponse($e->getMessage(), 422);
        }
    }
}
