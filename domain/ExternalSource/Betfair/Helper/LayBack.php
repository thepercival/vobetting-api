<?php
/**
 * Created by PhpStorm.
 * User: coen
 * Date: 6-3-18
 * Time: 19:55
 */

namespace VOBetting\ExternalSource\Betfair\Helper;

use VOBetting\BetLine;
use VOBetting\ExternalSource\Betfair\Helper as BetfairHelper;
use VOBetting\ExternalSource\Betfair\ApiHelper as BetfairApiHelper;
use VOBetting\ExternalSource\LayBack as ExternalSourceLayBack;
use VOBetting\LayBack as LayBackBase;
use VOBetting\ExternalSource\Betfair;
use Psr\Log\LoggerInterface;
use stdClass;
use Voetbal\Competition;
use Voetbal\Competitor as CompetitorBase;

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
        $competitionLayBacks = [];
        $betType = BetLine::_MATCH_ODDS;
        $events = $this->apiHelper->getEvents($competition->getLeague() );
        foreach ($events as $event) {
            $markets = $this->apiHelper->getMarkets($event->event->id, $betType);
            foreach ($markets as $market) {

                $marketBooks = $this->apiHelper->getMarketBooks($market->marketId);
                foreach ($marketBooks as $marketBook) {
                    foreach ($marketBook->runners as $runner) {
                        // var_dump($betLine->status); // IF CLOSED => UPDATE GAME!!
                        // var_dump($runnerOne->status); // "ACTIVE"
                        $backs = $runner->ex->availableToBack;
                        $lays = $runner->ex->availableToLay;
//                        $this->saveLayBacks( $this->getImportPeriod()->getStartDate(), $betLine, $backs, true );
//                        $this->saveLayBacks( $this->getImportPeriod()->getStartDate(), $betLine, $lays, false );
                    }
                }

//                foreach ($market->runners as $runner) {
//                    if ($runner->metadata->runnerId == $this->parent::THE_DRAW) {
//                        continue;
//                    }
//                    $layBack = ["id" => $runner->metadata->runnerId, "name" => $runner->runnerName ];
//                    if (in_array($layBack, $competitionLayBacks)) {
//                        continue;
//                    }
//                    $competitionLayBacks[] = $layBack;
//                }
            }
        }
        return $competitionLayBacks;
    }

//    /**
//     *
//     *
//     * @param array|stdClass[] $externalLayBacks
//     */
//    protected function setLayBacks(array $externalLayBacks)
//    {
//        $this->layBacks = [];
//
//        /** @var stdClass $externalLayBack */
//        foreach ($externalLayBacks as $externalLayBack) {
//            $name = $externalLayBack->id;
//            if ($this->hasName($this->layBacks, $name)) {
//                continue;
//            }
//            $layBack = $this->createLayBack($externalLayBack) ;
//            $this->layBacks[$layBack->getId()] = $layBack;
//        }
//    }
//
//    protected function createLayBack(stdClass $externalLayBack): LayBackBase
//    {
//        $layBack = new LayBackBase($externalLayBack->id, true);
//        $layBack->setId($externalLayBack->id);
//        return $layBack;
//    }


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
//    protected function getPoulePlace( Game $game, $competitor ): ?Place
//    {
//        $poulePlaces = $game->getPlaces()->map( function( GamePlace $gamePoulePlace ) {
//            return $gamePoulePlace->getPlace();
//        });
//        $foundPoulePlaces = $poulePlaces->filter( function( Place $poulePlace ) use ( $competitor ) {
//            return $poulePlace->getCompetitor() === $competitor;
//        });
//        return $foundPoulePlaces->first();
//    }
//
//    public function convertHomeAway( $homeAway )
//    {
//        if( $homeAway === 1 ) {
//            return Game::HOME;
//        }
//        else if( $homeAway === 2 ) {
//            return Game::AWAY;
//        }
//        else if( $homeAway === 3 ) {
//            return null;
//        }
//        throw new \Exception("betfair homeaway-value unknown", E_ERROR );
//    }
//
//
//
//    protected function saveLayBacks(
//        \DateTimeImmutable $dateTime,
//        BetLineBase $betLine,
//        $layBacks, $layBack
//    ) {
//        $bookmaker = $this->getBookmaker();
//        foreach( $layBacks as $layBackIt ){
//            $layBackNew = new LayBack( $dateTime, $betLine, $bookmaker, $this->externalSystemBase );
//            $layBackNew->setBack( $layBack );
//            $layBackNew->setPrice( $layBackIt->price );
//            $layBackNew->setSize( $layBackIt->size );
//            $this->layBackRepos->save($layBackNew);
//            break; // only first layBack, because is most interesting price/size
//        }
//    }
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
