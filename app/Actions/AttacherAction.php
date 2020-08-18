<?php
/**
 * Created by PhpStorm.
 * User: coen
 * Date: 22-5-18
 * Time: 12:23
 */

namespace App\Actions;

use App\Response\ErrorResponse;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Log\LoggerInterface;
use JMS\Serializer\SerializerInterface;
use SportsImport\ExternalSource\Repository as ExternalSourceRepository;
use SportsImport\Attacher\Repository as AttacherRepos;
use VOBetting\Attacher\Factory as AttacherFactory;
use SportsImport\Attacher;
use Sports\Repository as VoetbalRepository;
use Sports\Sport\Repository as SportRepository;
use SportsImport\Attacher\Sport\Repository as SportAttacherRepository;
use Sports\Association\Repository as AssociationRepository;
use SportsImport\Attacher\Association\Repository as AssociationAttacherRepository;
use Sports\Season\Repository as SeasonRepository;
use SportsImport\Attacher\Season\Repository as SeasonAttacherRepository;
use Sports\League\Repository as LeagueRepository;
use SportsImport\Attacher\League\Repository as LeagueAttacherRepository;
use Sports\Competition\Repository as CompetitionRepository;
use SportsImport\Attacher\Competition\Repository as CompetitionAttacherRepository;
use Sports\Competitor\Team\Repository as TeamCompetitorRepository;
use SportsImport\Attacher\Competitor\Team\Repository as TeamCompetitorAttacherRepository;
use VOBetting\Bookmaker\Repository as BookmakerRepository;
use VOBetting\Attacher\Bookmaker\Repository as BookmakerAttacherRepository;

final class AttacherAction extends Action
{
    /**
     * @var AttacherFactory
     */
    private $attacherFactory;
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
     * @var TeamCompetitorRepository
     */
    private $teamCompetitorAttacherRepos;
    /**
     * @var TeamCompetitorRepository
     */
    private $competitorRepos;
    /**
     * @var BookmakerAttacherRepository
     */
    private $bookmakerAttacherRepos;
    /**
     * @var BookmakerRepository
     */
    private $bookmakerRepos;

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
        TeamCompetitorAttacherRepository $teamCompetitorAttacherRepos,
        TeamCompetitorRepository $teamCompetitorRepos,
        BookmakerAttacherRepository $bookmakerAttacherRepos,
        BookmakerRepository $bookmakerRepos
    ) {
        parent::__construct($logger, $serializer);
        $this->attacherFactory = new AttacherFactory();
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
        $this->teamCompetitorAttacherRepos = $teamCompetitorAttacherRepos;
        $this->teamCompetitorRepos = $teamCompetitorRepos;
        $this->bookmakerAttacherRepos = $bookmakerAttacherRepos;
        $this->bookmakerRepos = $bookmakerRepos;
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

    public function fetchBookmakers(Request $request, Response $response, $args): Response
    {
        return $this->fetch($this->bookmakerAttacherRepos, $request, $response, $args);
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

    public function addBookmaker(Request $request, Response $response, $args): Response
    {
        return $this->add($this->bookmakerRepos, $this->bookmakerAttacherRepos, $request, $response, $args);
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

            /** @var Attacher $attacherSer */
            $attacherSer = $this->serializer->deserialize($this->getRawData(), 'SportsImport\Attacher', 'json');

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

    public function removeBookmaker(Request $request, Response $response, $args): Response
    {
        return $this->remove($this->bookmakerAttacherRepos, $request, $response, $args);
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
