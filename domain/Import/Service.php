<?php
/**
 * Created by PhpStorm.
 * User: coen
 * Date: 12-3-17
 * Time: 22:17
 */

namespace VOBetting\Import;

use Psr\Log\LoggerInterface;

use VOBetting\Attacher\Bookmaker\Repository as BookmakerAttacherRepository;
use VOBetting\BetLine\Repository as BetLineRepository;
use VOBetting\LayBack\Repository as LayBackRepository;
use SportsImport\Attacher\Competitor\TeamRepository as TeamCompetitorAttacherRepository;
use SportsImport\Attacher\Competition\Repository as CompetitionAttacherRepository;
use SportsImport\ExternalSource\Competition as ExternalSourceCompetition;
use SportsImport\ExternalSource\Game as ExternalSourceGame;
use SportsImport\ExternalSource\Structure as ExternalSourceStructure;
use SportsImport\Service as ImportService;
use SportsImport\ExternalSource\Implementation as ExternalSourceImplementation;
use Sports\Structure\Repository as StructureRepository;
use VOBetting\ExternalSource\LayBack as ExternalSourceLayBack;
use Sports\State;
use Sports\Game\Repository as GameRepository;

class Service extends ImportService
{
    public function __construct( LoggerInterface $logger ) {
        parent::__construct( $logger );
    }

    public function importLayBacks(
        ExternalSourceImplementation $externalSourceImplementation,
        GameRepository $gameRepos,
        LayBackRepository $layBackRepos,
        BetLineRepository $betLineRepos,
        BookmakerAttacherRepository $bookmakerAttacherRepos,
        CompetitorAttacherRepository $competitorAttacherRepos,
        CompetitionAttacherRepository $competitionAttacherRepos
    ) {
        if (!($externalSourceImplementation instanceof ExternalSourceLayBack)
            || !($externalSourceImplementation instanceof ExternalSourceCompetition)) {
            return;
        }
        $importLayBackService = new Service\LayBack(
            $layBackRepos,
            $betLineRepos,
            $gameRepos,
            $bookmakerAttacherRepos,
            $competitorAttacherRepos,
            $this->logger
        );

        $filter = ["externalSource" => $externalSourceImplementation->getExternalSource() ];
        $competitionAttachers = $competitionAttacherRepos->findBy($filter);
        foreach ($competitionAttachers as $competitionAttacher) {
            $externalCompetition = $externalSourceImplementation->getCompetition($competitionAttacher->getExternalId());
            if ($externalCompetition === null) {
                continue;
            }
            $competition = $competitionAttacher->getImportable();
            if ( $gameRepos->hasCompetitionGames($competition, State::Created + State::InProgress) === false ) {
                continue;
            }
            // $importGameService->setPoule( );
            $importLayBackService->import(
                $externalSourceImplementation->getExternalSource(),
                $externalSourceImplementation->getLayBacks($externalCompetition)
            );
        }
    }
}
