<?php

/**
 * Created by PhpStorm.
 * User: coen
 * Date: 14-3-18
 * Time: 12:02
 */

namespace VOBetting\External\System\Betfair;

use VOBetting\External\System\Importer\BetLine as BetLineImporter;
use Voetbal\External\System as ExternalSystemBase;
use Voetbal\League;
use Voetbal\Game;
use VOBetting\External\System\Betfair as ExternalSystemBetfair;
use VOBetting\BetLine\Repository as BetLineRepos;
use Voetbal\External\League as ExternalLeague;
use League\Period\Period;
use VOBetting\BetLine as BetLineBase;
use Voetbal\Competition\Repository as CompetitionRepos;
use Voetbal\Game\Repository as GameRepos;
use Voetbal\External\Competitor\Repository as ExternalCompetitorRepos;
use VOBetting\LayBack\Repository as LayBackRepos;
use VOBetting\Bookmaker\Repository as BookmakerRepos;
use VOBetting\LayBack;
use Monolog\Logger;
use VOBetting\Bookmaker;
use Voetbal\Place;
use Voetbal\Game\Place as GamePlace;
use Voetbal\State;

class BetLine implements BetLineImporter
{
    /**
     * @var ExternalSystemBase
     */
    private $externalSystemBase;
    /**
     * @var ApiHelper
     */
    private $apiHelper;
    /**
     * @var GameRepos
     */
    private $gameRepos;
    /**
     * @var ExternalCompetitorRepos
     */
    private $externalCompetitorRepos;
    /**
     * @var BetLineRepos
     */
    private $repos;
    /**
     * @var CompetitionRepos
     */
    private $competitionRepos;
    /**
     * @var LayBackRepos
     */
    private $layBackRepos;
    /**
     * @var BookmakerRepos
     */
    private $bookmakerRepos;
    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var int
     */
    private $maxDaysBeforeImport;
    /**
     * @var Period
     */
    private $period;


    public function __construct(
        ExternalSystemBase $externalSystemBase,
        ApiHelper $apiHelper,
        BetLineRepos $repos,
        CompetitionRepos $competitionRepos,
        GameRepos $gameRepos,
        ExternalCompetitorRepos $externalCompetitorRepos,
        LayBackRepos $layBackRepos,
        BookmakerRepos $bookmakerRepos,
        Logger $logger

    ) {
        $this->externalSystemBase = $externalSystemBase;
        $this->apiHelper = $apiHelper;
        $this->repos = $repos;
        $this->competitionRepos = $competitionRepos;
        $this->gameRepos = $gameRepos;
        $this->externalCompetitorRepos = $externalCompetitorRepos;
        $this->layBackRepos = $layBackRepos;
        $this->bookmakerRepos = $bookmakerRepos;
        $this->logger = $logger;
    }

    public function get( ExternalLeague $externalLeague )
    {
        return $this->apiHelper->getEvents( $externalLeague, $this->getImportPeriod() );
    }

    public function getId( $externalSystemBetLine )
    {
        throw new \Exception("notimplyet", E_ERROR );
    }

    private function getBookmaker(): Bookmaker
    {
        return $this->bookmakerRepos->findOneBy( array("name" => "Betfair") );
    }

    public function process( League $league, $externalSystemEvent, $betType ) {
        $markets = $this->apiHelper->getMarkets( $externalSystemEvent->event->id, $betType );
        $startDateTime = \DateTimeImmutable::createFromFormat('Y-m-d\TH:i:s.u\Z', $externalSystemEvent->event->openDate);

        foreach ($markets as $market) {
            $game = $this->getGame($league, $startDateTime, $market->runners);
            if ( $game === null ) {
                continue;
            }

            $marketBooks = $this->apiHelper->getMarketBooks($market->marketId);
            foreach ($marketBooks as $marketBook) {
                foreach ($marketBook->runners as $runner) {
                    $betLine = $this->syncBetLine($game, $betType, $runner);
                    if ($betLine === null) {
                        continue;
                    }
                    // var_dump($betLine->status); // IF CLOSED => UPDATE GAME!!
                    // var_dump($runnerOne->status); // "ACTIVE"
                    $backs = $runner->ex->availableToBack;
                    $lays = $runner->ex->availableToLay;
                    $this->saveLayBacks( $this->getImportPeriod()->getStartDate(), $betLine, $backs, true );
                    $this->saveLayBacks( $this->getImportPeriod()->getStartDate(), $betLine, $lays, false );
                }
            }
        }
    }

    public function setMaxDaysBeforeImport( int $maxDaysBeforeImport ) {
        $this->maxDaysBeforeImport = $maxDaysBeforeImport;
    }

    protected function getImportPeriod() {
        if( $this->period === null ) {
            $now = new \DateTimeImmutable();
            $this->period = new Period( $now, $now->modify("+".$this->maxDaysBeforeImport." days") );
        }
        return $this->period;
    }

    protected function syncBetLine( Game $game, $betType, $runner)
    {
        $poulePlace = null;
        if( $runner->selectionId != ExternalSystemBetfair::THE_DRAW ) { // the draw
            $competitor = $this->getCompetitorFromExternalId($runner->selectionId);
            if( $competitor === null ) {
                return null;
            }
            $poulePlace = $this->getPoulePlace( $game, $competitor );
        }
        $betLine = $this->repos->findOneBy(array(
            "game" => $game,
            "betType" => $betType,
            "poulePlace" => $poulePlace
        ));
        if( $betLine === null ) {
            $betLine = new BetLineBase($game, $betType);
            $betLine->setPoulePlace($poulePlace);
        }
        // maybe save close state here
        return $this->repos->save($betLine);
    }

    protected function getPoulePlace( Game $game, $competitor ): ?Place
    {
        $poulePlaces = $game->getPlaces()->map( function( GamePlace $gamePoulePlace ) {
            return $gamePoulePlace->getPlace();
        });
        $foundPoulePlaces = $poulePlaces->filter( function( Place $poulePlace ) use ( $competitor ) {
            return $poulePlace->getCompetitor() === $competitor;
        });
        return $foundPoulePlaces->first();
    }

    public function convertHomeAway( $homeAway )
    {
        if( $homeAway === 1 ) {
            return Game::HOME;
        }
        else if( $homeAway === 2 ) {
            return Game::AWAY;
        }
        else if( $homeAway === 3 ) {
            return null;
        }
        throw new \Exception("betfair homeaway-value unknown", E_ERROR );
    }



    protected function saveLayBacks(
        \DateTimeImmutable $dateTime,
        BetLineBase $betLine,
        $layBacks, $layBack
    ) {
        $bookmaker = $this->getBookmaker();
        foreach( $layBacks as $layBackIt ){
            $layBackNew = new LayBack( $dateTime, $betLine, $bookmaker, $this->externalSystemBase );
            $layBackNew->setBack( $layBack );
            $layBackNew->setPrice( $layBackIt->price );
            $layBackNew->setSize( $layBackIt->size );
            $this->layBackRepos->save($layBackNew);
            break; // only first layBack, because is most interesting price/size
        }
    }

    protected function syncStartDateTime( Game $game, \DateTimeImmutable $startDateTime)
    {
        if( $game->getStartDateTime() != $startDateTime ) {
            $game->setStartDateTime( $startDateTime );
            return $this->gameRepos->save( $game );
        }
        return $game;
    }

    protected function getCompetitorFromExternalId( $externalId )
    {
        $competitor = $this->externalCompetitorRepos->findImportable( $this->externalSystemBase, $externalId );
        if( $competitor === null ) {
            $this->logger->notice("competitor not found for externalid " . $externalId . " and externalSystem " . $this->externalSystemBase->getName() );
        }
        return $competitor;
    }

    protected function getGame( League $league, \DateTimeImmutable $startDateTime, $runners )
    {
        $competition = $this->competitionRepos->findOneByLeagueAndDate( $league,  $startDateTime );

        if( $competition === false ) {
            $this->logger->notice("competition not found for league " . $league->getName() . " and date " . $startDateTime->format(\DATE_ISO8601));
            return null;
        }

        $homeRunnerId = null; $awayRunnerId = null; $drawRunnerId = null;
        foreach( $runners as $runner ) {
            $homeAway = $this->convertHomeAway( $runner->sortPriority );
            if( $homeAway === Game::HOME ) {
                $homeRunnerId = $runner->metadata->runnerId;
            }
            else if( $homeAway === Game::AWAY ) {
                $awayRunnerId = $runner->metadata->runnerId;
            }
            else {
                $drawRunnerId = $runner->metadata->runnerId;
            }
            // use $runner->selectionId as marketbook
        }

        $homeCompetitor = $this->getCompetitorFromExternalId( $homeRunnerId );
        if( $homeCompetitor === null ) {
            return null;
        }
        $awayCompetitor = $this->getCompetitorFromExternalId( $awayRunnerId );
        if( $awayCompetitor === null  ) {
            return null;
        }

        $states = State::Created + State::InProgress;
        $games = $this->gameRepos->findByExt( $homeCompetitor, $awayCompetitor, $competition, $states );
        if( $games === null ) {
            $this->logger->notice("game not found for homecompetitor " . $homeCompetitor->getName() . ",awaycompetitor " . $awayCompetitor->getName() . ", competition " . $competition->getName() . " and states " . $states );
        }
        return reset( $games );
    }
}