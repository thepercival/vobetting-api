<?php
/**
 * Created by PhpStorm.
 * User: coen
 * Date: 6-3-18
 * Time: 19:55
 */

namespace VOBetting\ExternalSource\TheOddsApi\Helper;

use DateTimeImmutable;
use VOBetting\BetLine;
use VOBetting\ExternalSource\TheOddsApi\Helper as TheOddsApiHelper;
use VOBetting\ExternalSource\TheOddsApi\ApiHelper as TheOddsApiApiHelper;
use VOBetting\ExternalSource\LayBack as ExternalSourceLayBack;
use VOBetting\LayBack as LayBackBase;
use VOBetting\Bookmaker;
use VOBetting\ExternalSource\TheOddsApi;
use Psr\Log\LoggerInterface;
use stdClass;
use Sports\Competition;
use Sports\Competitor;
use Sports\Game as GameBase;
use Sports\Place;
use Sports\Poule;

class LayBack extends TheOddsApiHelper implements ExternalSourceLayBack
{
    public function __construct(
        TheOddsApi $parent,
        TheOddsApiApiHelper $apiHelper,
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

//    public function getLayBack(Competition $competition, $id): ?LayBackBase
//    {
//        $competitionLayBacks = $this->getLayBacksHelper($competition);
//        if (array_key_exists($id, $competitionLayBacks)) {
//            return $competitionLayBacks[$id];
//        }
//        return null;
//    }

    protected function getLayBacksHelper(Competition $competition): array
    {
        $association = $competition->getLeague()->getAssociation();
        $competitors = $this->parent->getCompetitors( $competition );
        if( count($competitors) === 0 ) {
            return []; // no competitors
        }
        $dummyPoule = $this->createDummyPoule($competition, $competitors);
        $competitionLayBacks = [];
        $events = $this->apiHelper->getEventsByLeague($competition->getLeague() );
        foreach ($events as $event) {
            $competitors = $this->apiHelper->getCompetitors( $association, $event );
            $homeCompetitor = $this->apiHelper->convertCompetitorData( $association, $event->home_team );
            $startDateTime = new DateTimeImmutable('@' . $event->commence_time);
            $game = $this->createGame( $dummyPoule, $startDateTime, $competitors);
            if( $game === null ) {
                continue;
            }
            $betLine = new BetLine( $game, BetLine::_MATCH_ODDS );
            $eventCompetitorsSimple = $this->apiHelper->getCompetitorsSimple( $association, $event );

            /** @var stdClass $externalBookmaker */
            foreach ($event->sites as $externalBookmaker) {
                $bookmaker = $this->apiHelper->convertBookmakerData( $externalBookmaker );
                if( $bookmaker->getExchange() ) {
                    continue;
                }
                if( !property_exists( $externalBookmaker, "odds" ) || !property_exists( $externalBookmaker->odds, "h2h" ) ) {
                    continue;
                }
                $odds = $externalBookmaker->odds->h2h;

                $eventCompetitors = $eventCompetitorsSimple;
                $odds1 = array_shift( $odds );
                $eventCompetitor1 = array_shift( $eventCompetitors );
                $odds2 = array_shift( $odds );
                $eventCompetitor2 = array_shift( $eventCompetitors );
                $drawOdds = array_shift( $odds );
                if( $drawOdds === null ) {
                    continue;
                }
                $runnerHomeAway =  $eventCompetitor1->getId() === $homeCompetitor->getId() ? GameBase::HOME : GameBase::AWAY;
                $competitionLayBacks[] = $this->createLayBackFromExternal( $betLine, $bookmaker, LayBackBase::BACK, $odds1, $runnerHomeAway );
                $runnerHomeAway =  $eventCompetitor2->getId() === $homeCompetitor->getId() ? GameBase::HOME : GameBase::AWAY;
                $competitionLayBacks[] = $this->createLayBackFromExternal( $betLine, $bookmaker, LayBackBase::BACK, $odds2, $runnerHomeAway );
                $competitionLayBacks[] = $this->createLayBackFromExternal( $betLine, $bookmaker, LayBackBase::BACK, $drawOdds, null );
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
        $places = $poule->getPlaces()->filter(function (Place $place) use ($competitor): bool {
            return $place->getCompetitor() !== null && $place->getCompetitor()->getId() === $competitor->getId();
        });
        if ($places->count() !== 1) {
            return null;
        }
        return $places->first();
    }

    protected function createLayBackFromExternal(BetLine $betLine, Bookmaker $bookmaker, bool $layOrBack, float $price, bool $runnerHomeAway = null): LayBackBase
    {
        $layBackNew = new LayBackBase( new DateTimeImmutable(), $betLine, $bookmaker, $runnerHomeAway );
        $layBackNew->setBack( $layOrBack );
        $layBackNew->setPrice( $price );
        $layBackNew->setSize( 0 );
        return $layBackNew;
    }
}
