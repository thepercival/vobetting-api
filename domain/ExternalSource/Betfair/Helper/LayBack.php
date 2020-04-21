<?php
/**
 * Created by PhpStorm.
 * User: coen
 * Date: 6-3-18
 * Time: 19:55
 */

namespace VOBetting\ExternalSource\Betfair\Helper;

use DateTime;
use DateTimeImmutable;
use VOBetting\BetLine;
use VOBetting\ExternalSource\Betfair\Helper as BetfairHelper;
use VOBetting\ExternalSource\Betfair\ApiHelper as BetfairApiHelper;
use VOBetting\ExternalSource\LayBack as ExternalSourceLayBack;
use VOBetting\LayBack as LayBackBase;
use VOBetting\ExternalSource\Betfair;
use Psr\Log\LoggerInterface;
use stdClass;
use Voetbal\Competition;
use Voetbal\Competitor;
use Voetbal\Competitor as CompetitorBase;
use Voetbal\Game as GameBase;
use Voetbal\Place;
use Voetbal\Poule;

class LayBack extends BetfairHelper implements ExternalSourceLayBack
{
    public function __construct(
        Betfair $parent,
        BetfairApiHelper $apiHelper,
        LoggerInterface $logger
    ) {
        parent::__construct(
            $parent,
            $apiHelper,
            $logger
        );
    }

    public function getLayBacks(Competition $competition): array
    {
        return array_values($this->getLayBacksHelper($competition));
    }

    public function getLayBack(Competition $competition, $id): ?LayBackBase
    {
        $competitionLayBacks = $this->getLayBacksHelper($competition);
        if (array_key_exists($id, $competitionLayBacks)) {
            return $competitionLayBacks[$id];
        }
        return null;
    }

    protected function getLayBacksHelper(Competition $competition): array
    {
        $association = $competition->getLeague()->getAssociation();
        $dummyPoule = $this->createDummyPoule($competition);
        $competitionLayBacks = [];
        $betType = BetLine::_MATCH_ODDS;
        $events = $this->apiHelper->getEvents($competition->getLeague() );
        foreach ($events as $event) {
            $startDateTime = DateTimeImmutable::createFromFormat( $this->apiHelper->getDateFormat(), $event->event->openData );

            $markets = $this->apiHelper->getMarkets($event->event->id, $betType);
            foreach ($markets as $market) {
                $competitors = $this->apiHelper->getCompetitors( $association, $market->runners );
                $game = $this->createGame( $dummyPoule, $startDateTime, $competitors);
                if( $game === null ) {
                    continue;
                }
                $betLine = new BetLine( $game, $this->apiHelper->convertBetTypeBack( $market->marketName ) );

                $marketBooks = $this->apiHelper->getMarketBooks($market->marketId);
                foreach ($marketBooks as $marketBook) {
                    if( $marketBook->status !== "OPEN") {
                        continue;
                    }
                    foreach ($marketBook->runners as $runner) {
                        if( $runner->status !== "ACTIVE") {
                            continue;
                        }
                        $externalLayBacks = [
                            LayBackBase::BACK => $runner->ex->availableToBack,
                            LayBackBase::LAY => $runner->ex->availableToLay
                        ];
                        /** @var bool $layBackValue */
                        foreach( $externalLayBacks as $layBackValue => $externalLayBack) {
                            $competitionLayBacks[] = $this->createLayBackFromExternal( $betLine, $layBackValue, $externalLayBack );
                        }
                        // var_dump($betLine->status); // IF CLOSED => UPDATE GAME!!
                        // var_dump($runnerOne->status); // "ACTIVE"
                    }
                }
            }
        }
        return $competitionLayBacks;
    }

    /**
     * @param Poule $dummyPoule
     * @param DateTimeImmutable $dateTime
     * @param array|Competitor[][] $competitors
     * @return GameBase|null
     */
    protected function createGame(Poule $dummyPoule, DateTimeImmutable $dateTime, array $competitors ): ?GameBase {
        $game = new GameBase($dummyPoule, 1, $dateTime);
        /** @var bool $homeAway */
        foreach( $competitors as $homeAway => $homeAwayCompetitors ) {
            foreach( $homeAwayCompetitors as $competitor ) {
                $place = $this->getPlaceFromPoule($dummyPoule, $competitor);
                if ($place === null) {
                    return null;
                }
                $game->addPlace($place, $homeAway);
            }
        }
        return $game;
    }

    protected function getPlaceFromPoule(Poule $poule, Competitor $competitor): ?Place
    {
        $places = $poule->getPlaces()->filter(function (Place $place) use ($competitor) {
            return $place->getCompetitor() && $place->getCompetitor()->getId() === $competitor->getId();
        });
        if ($places->count() !== 1) {
            return null;
        }
        return $places->first();
    }

    protected function createLayBackFromExternal(BetLine $betLine, bool $layOrBack , stdClass $externalLayBack): LayBackBase
    {
        $bookMaker = $this->parent->getBookmaker($this->parent::NAME);
        $layBackNew = new LayBackBase( new DateTimeImmutable(), $betLine, $bookMaker );
        $layBackNew->setBack( $layOrBack );
        $layBackNew->setPrice( $externalLayBack->price );
        $layBackNew->setSize( $externalLayBack->size );
        return $layBackNew;
    }

//
//    public function process( League $league, $externalSystemEvent, $betType ) {
//        $markets = $this->apiHelper->getMarkets( $externalSystemEvent->event->id, $betType );
//        $startDateTime = \DateTimeImmutable::createFromFormat('Y-m-d\TH:i:s.u\Z', $externalSystemEvent->event->openDate);
//
//        foreach ($markets as $market) {
//            $game = $this->getGame($league, $startDateTime, $market->runners);
//            if ( $game === null ) {
//                continue;
//            }
//
//            $marketBooks = $this->apiHelper->getMarketBooks($market->marketId);
//            foreach ($marketBooks as $marketBook) {
//                foreach ($marketBook->runners as $runner) {
//                    $betLine = $this->syncBetLine($game, $betType, $runner);
//                    if ($betLine === null) {
//                        continue;
//                    }
//                    // var_dump($betLine->status); // IF CLOSED => UPDATE GAME!!
//                    // var_dump($runnerOne->status); // "ACTIVE"
//                    $backs = $runner->ex->availableToBack;
//                    $lays = $runner->ex->availableToLay;
//                    $this->saveLayBacks( $this->getImportPeriod()->getStartDate(), $betLine, $backs, true );
//                    $this->saveLayBacks( $this->getImportPeriod()->getStartDate(), $betLine, $lays, false );
//                }
//            }
//        }
//    }
//
//    public function setMaxDaysBeforeImport( int $maxDaysBeforeImport ) {
//        $this->maxDaysBeforeImport = $maxDaysBeforeImport;
//    }
//
//    protected function getImportPeriod() {
//        if( $this->period === null ) {
//            $now = new \DateTimeImmutable();
//            $this->period = new Period( $now, $now->modify("+".$this->maxDaysBeforeImport." days") );
//        }
//        return $this->period;
//    }
//
//    protected function syncBetLine( Game $game, $betType, $runner)
//    {
//        $poulePlace = null;
//        if( $runner->selectionId != ExternalSystemBetfair::THE_DRAW ) { // the draw
//            $competitor = $this->getCompetitorFromExternalId($runner->selectionId);
//            if( $competitor === null ) {
//                return null;
//            }
//            $poulePlace = $this->getPoulePlace( $game, $competitor );
//        }
//        $betLine = $this->repos->findOneBy(array(
//            "game" => $game,
//            "betType" => $betType,
//            "poulePlace" => $poulePlace
//        ));
//        if( $betLine === null ) {
//            $betLine = new BetLineBase($game, $betType);
//            $betLine->setPoulePlace($poulePlace);
//        }
//        // maybe save close state here
//        return $this->repos->save($betLine);
//    }
//
//
//
//    protected function syncStartDateTime( Game $game, \DateTimeImmutable $startDateTime)
//    {
//        if( $game->getStartDateTime() != $startDateTime ) {
//            $game->setStartDateTime( $startDateTime );
//            return $this->gameRepos->save( $game );
//        }
//        return $game;
//    }
//
//    protected function getCompetitorFromExternalId( $externalId )
//    {
//        $competitor = $this->externalCompetitorRepos->findImportable( $this->externalSystemBase, $externalId );
//        if( $competitor === null ) {
//            $this->logger->notice("competitor not found for externalid " . $externalId . " and external source " . $this->externalSystemBase->getName() );
//        }
//        return $competitor;
//    }
//
//    protected function getGame( League $league, \DateTimeImmutable $startDateTime, $runners )
//    {
//        $competition = $this->competitionRepos->findOneByLeagueAndDate( $league,  $startDateTime );
//
//        if( $competition === false ) {
//            $this->logger->notice("competition not found for league " . $league->getName() . " and date " . $startDateTime->format(\DATE_ISO8601));
//            return null;
//        }
//
//        $homeRunnerId = null; $awayRunnerId = null; $drawRunnerId = null;
//        foreach( $runners as $runner ) {
//            $homeAway = $this->convertHomeAway( $runner->sortPriority );
//            if( $homeAway === Game::HOME ) {
//                $homeRunnerId = $runner->metadata->runnerId;
//            }
//            else if( $homeAway === Game::AWAY ) {
//                $awayRunnerId = $runner->metadata->runnerId;
//            }
//            else {
//                $drawRunnerId = $runner->metadata->runnerId;
//            }
//            // use $runner->selectionId as marketbook
//        }
//
//        $homeCompetitor = $this->getCompetitorFromExternalId( $homeRunnerId );
//        if( $homeCompetitor === null ) {
//            return null;
//        }
//        $awayCompetitor = $this->getCompetitorFromExternalId( $awayRunnerId );
//        if( $awayCompetitor === null  ) {
//            return null;
//        }
//
//        $states = State::Created + State::InProgress;
//        $games = $this->gameRepos->findByExt( $homeCompetitor, $awayCompetitor, $competition, $states );
//        if( $games === null ) {
//            $this->logger->notice("game not found for homecompetitor " . $homeCompetitor->getName() . ",awaycompetitor " . $awayCompetitor->getName() . ", competition " . $competition->getName() . " and states " . $states );
//        }
//        return reset( $games );
//    }
}
