<?php

namespace VOBetting\Import\Service;

use DateTimeImmutable;
use VOBetting\BetLine;
use VOBetting\BetLine\Repository as BetLineRepository;
use VOBetting\Bookmaker;
use VOBetting\Attacher\Bookmaker\Repository as BookmakerAttacherRepository;
use Voetbal\Competition;
use Voetbal\Competitor;
use Voetbal\Attacher\Competitor\Repository as CompetitorAttacherRepository;
use Voetbal\Game;
use Voetbal\State as VoetbalState;
use Voetbal\Import\ImporterInterface;
use Voetbal\ExternalSource;
use VOBetting\LayBack\Repository as LayBackRepository;
use VOBetting\LayBack as LayBackBase;
use VOBetting\LayBack\Service as LayBackService;
use Psr\Log\LoggerInterface;

class LayBack implements ImporterInterface
{
    /**
     * @var LayBackRepository
     */
    protected $layBackRepos;
    /**
     * @var BetLineRepository
     */
    protected $betLineRepos;
    /**
     * @var BookmakerAttacherRepository
     */
    protected $bookmakerAttacherRepos;
    /**
     * @var CompetitorAttacherRepository
     */
    protected $competitorAttacherRepos;
    /**
     * @var LoggerInterface
     */
    private $logger;

    // public const MAX_DAYS_BACK = 8;

    public function __construct(
        LayBackRepository $layBackRepos,
        BetLineRepository $betLineRepos,
        BookmakerAttacherRepository $bookmakerAttacherRepos,
        CompetitorAttacherRepository $competitorAttacherRepos,
        LoggerInterface $logger
    ) {
        $this->logger = $logger;
        $this->layBackRepos = $layBackRepos;
        $this->betLineRepos = $betLineRepos;
        $this->bookmakerAttacherRepos = $bookmakerAttacherRepos;
        $this->competitorAttacherRepos = $competitorAttacherRepos;
    }
//
//    protected function getDeadLine(): DateTimeImmutable {
//        return (new DateTimeImmutable())->modify("-" . static::MAX_DAYS_BACK . " days");
//    }


    /**
     * @param ExternalSource $externalSource
     * @param array|LayBackBase[] $externalSourceLayBacks
     * @throws \Exception
     */
    public function import(ExternalSource $externalSource, array $externalSourceLayBacks)
    {
        foreach ($externalSourceLayBacks as $externalSourceLayBack) {
            $layBack = $this->createLayBack($externalSource, $externalSourceLayBack);
            if ($layBack === null) {
                continue;
            }
            $this->layBackRepos->save($layBack);
        }
    }

    protected function createLayBack(ExternalSource $externalSource, LayBackBase $externalSourceLayBack): ?LayBackBase
    {
        $betLine = $this->getBetLineFromExternal($externalSource, $externalSourceLayBack->getBetLine() );
        if ($betLine === null) {
            $betLine = $this->createBetLine($externalSource, $externalSourceLayBack->getBetLine() );
            $this->betLineRepos->save($betLine);
        }
        $bookmaker = $this->getBookmakerFromExternal($externalSource, $externalSourceLayBack->getBookmaker() );
        if ($bookmaker === null) {
            return null;
        }

        $layBack = new LayBackBase(
            $externalSourceLayBack->getDateTime(),
            $betLine,
            $bookmaker);

        // $layBack->setBack( ? );
        // bool $back;
        // float $price;
        // double $size;

        return $layBack;
    }

    protected function createBetLine(ExternalSource $externalSource, BetLine $externalSourceBetLine): ?BetLine
    {
        $betType = 0;
        $game = new Game();
        $betLine = new BetLine($game, $betType);
        // $betLine->setPlace( ? );
        return $betLine;
    }

    protected function getBetLineFromExternal(ExternalSource $externalSource, BetLine $betLine): ?BetLine
    {
        return null;
//        $externalCompetition = $externalPoule->getRound()->getNumber()->getCompetition();
//
//        $competition = $this->competitionAttacherRepos->findImportable(
//            $externalSource,
//            $externalCompetition->getId()
//        );
//        if ($competition === null) {
//            return null;
//        }
//        $structure = $this->structureRepos->getStructure($competition);
//        if ($structure === null) {
//            return null;
//        }
//        return $structure->getFirstRoundNumber()->getRounds()->first()->getPoules()->first();
    }

    protected function getBookmakerFromExternal(ExternalSource $externalSource, Bookmaker $externalBookmaker): ?Bookmaker
    {
        return $this->bookmakerAttacherRepos->findImportable( $externalSource, $externalBookmaker->getId() );
    }
}
