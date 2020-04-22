<?php

namespace VOBetting\Import\Service;

use DateTimeImmutable;
use League\Period\Period;
use VOBetting\BetLine;
use VOBetting\BetLine\Repository as BetLineRepository;
use VOBetting\Bookmaker;
use VOBetting\Attacher\Bookmaker\Repository as BookmakerAttacherRepository;
use Voetbal\Competition;
use Voetbal\Competitor;
use Voetbal\Attacher\Competitor\Repository as CompetitorAttacherRepository;
use Voetbal\Game;
use Voetbal\Game\Repository as GameRepository;
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
     * @var GameRepository
     */
    protected $gameRepos;
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
        GameRepository $gameRepos,
        BookmakerAttacherRepository $bookmakerAttacherRepos,
        CompetitorAttacherRepository $competitorAttacherRepos,
        LoggerInterface $logger
    ) {
        $this->logger = $logger;
        $this->layBackRepos = $layBackRepos;
        $this->betLineRepos = $betLineRepos;
        $this->gameRepos = $gameRepos;
        $this->bookmakerAttacherRepos = $bookmakerAttacherRepos;
        $this->competitorAttacherRepos = $competitorAttacherRepos;
    }

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
        $externalBetLine = $externalSourceLayBack->getBetLine();
        $game = $this->getGameFromExternal($externalSource, $externalBetLine->getGame() );
        if( $game === null ) {
            return null;
        }
        $betLine = $this->getBetLine($game, $externalBetLine->getBetType() );
        if ($betLine === null) {
            $betLine = new BetLine($game, $externalBetLine->getBetType() );
            // $betLine->setPlace( ? );
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

        $layBack->setBack( $externalSourceLayBack->getBack() );
        $layBack->setPrice( $externalSourceLayBack->getPrice() );
        $layBack->setSize( $externalSourceLayBack->getSize() );

        return $layBack;
    }

    protected function getBetLine(Game $game, int $betType): ?BetLine
    {
        return $this->betLineRepos->findOneBy( ["game" => $game, "betType" => $betType] );
    }

    protected function getGameFromExternal(ExternalSource $externalSource, Game $externalGame): ?Game
    {
        $externalHomeCompetitor = $externalGame->getPlaces(Game::HOME)->first()->getPlace()->getCompetitor();
        $externalAwayCompetitor = $externalGame->getPlaces(Game::AWAY)->first()->getPlace()->getCompetitor();

        $homeCompetitor = $this->getCompetitorFromExternal( $externalSource, $externalHomeCompetitor );
        $awayCompetitor = $this->getCompetitorFromExternal( $externalSource, $externalAwayCompetitor );
        $period = new Period(
            $externalGame->getStartDateTime()->modify("-1 days"),
            $externalGame->getStartDateTime()->modify("+1 days")
        );
        return $this->gameRepos->findOneByExt( $homeCompetitor, $awayCompetitor, $period);
    }

    protected function getCompetitorFromExternal(ExternalSource $externalSource, Competitor $externalCompetitor): ?Competitor
    {
        return $this->competitorAttacherRepos->findImportable( $externalSource, $externalCompetitor->getId() );
    }

    protected function getBookmakerFromExternal(ExternalSource $externalSource, Bookmaker $externalBookmaker): ?Bookmaker
    {
        return $this->bookmakerAttacherRepos->findImportable( $externalSource, $externalBookmaker->getId() );
    }
}
