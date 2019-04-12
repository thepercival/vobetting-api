<?php
/**
 * Created by PhpStorm.
 * User: coen
 * Date: 9-4-18
 * Time: 10:48
 */

namespace VOBetting\External\System\APIFootball;

use PeterColes\Betfair\Betfair as PeterColesBetfair;
use VOBetting\External\System\Importer\BetLine as BetLineImporter;
use Voetbal\External\System as ExternalSystemBase;
use Voetbal\League;
use Voetbal\Game;
use VOBetting\External\System\APIFootball as ExternalSystemAPIFootball;
use VOBetting\BetLine\Repository as BetLineRepos;
use Voetbal\External\League as ExternalLeague;
use League\Period\Period;
use VOBetting\BetLine as BetLineBase;
use Voetbal\Competition\Repository as CompetitionRepos;
use Voetbal\Game\Repository as GameRepos;
use Voetbal\External\Competitor\Repository as ExternalCompetitorRepos;
use VOBetting\LayBack\Repository as LayBackRepos;
use VOBetting\LayBack;
use Monolog\Logger;

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
        Logger $logger

    ) {
        $this->externalSystemBase = $externalSystemBase;
        $this->apiHelper = $apiHelper;
        $this->repos = $repos;
        $this->competitionRepos = $competitionRepos;
        $this->gameRepos = $gameRepos;
        $this->externalCompetitorRepos = $externalCompetitorRepos;
        $this->layBackRepos = $layBackRepos;
        $this->logger = $logger;
    }

    public function get( ExternalLeague $externalLeague )
    {
        $period = "&from=" . $this->getImportPeriod()->getStartDate()->format($this->apiHelper->getDateFormat());
        $period .= "&to=" . $this->getImportPeriod()->getEndDate()->format($this->apiHelper->getDateFormat());
        $events = $this->apiHelper->getData("action=get_events&league_id=".$externalLeague->getExternalId() . $period );
        if( $events === null ) {
            return [];
        }
        return $events;
    }

    public function getId( $externalSystemBetLine )
    {
        throw new \Exception("notimplyet", E_ERROR );
    }

    public function getOdds(  )
    {
        $period = "&from=" . $this->getOddsPeriod()->getStartDate()->format($this->apiHelper->getDateFormat());
        $period .= "&to=" . $this->getOddsPeriod()->getEndDate()->format($this->apiHelper->getDateFormat());
        $period .= "&match_id=284044";
        $odds = $this->apiHelper->getData("action=get_odds" . $period );
        if( $odds === null ) {
            return [];
        }
        return $odds;
    }

    public function process( League $league, $externalSystemEvent, $betType ) {
        // $odds = $this->getOdds( $externalSystemEvent->event->id, $betType );
        // var_dump($odds); die();
//        $startDateTime = \DateTimeImmutable::createFromFormat('Y-m-d\TH:i:s.u\Z', $externalSystemEvent->event->openDate);
//
//        foreach ($markets as $market) {
//            $game = $this->getGame($league, $startDateTime, $market->runners);
//            if ( $game === null ) {
//                continue;
//            }
//
//            $marketBooks = $this->getMarketBooks($market->marketId);
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

    protected function getOddsPeriod() {
        $now = new \DateTimeImmutable();
        return new Period( $now, $now->modify("+4 days") );
    }

    protected function syncBetLine( Game $game, $betType, $runner)
    {
        throw new \Exception("not implemented yet", E_ERROR );
        /*$poulePlace = null;
        if( $runner->selectionId !== ExternalSystemBetfair::THE_DRAW ) { // the draw
            $competitor = $this->getCompetitorFromExternalId($runner->selectionId);
            if( $competitor === null ) {
                return null;
            }
            $poulePlace = $game->getPoulePlaceForCompetitor($competitor);
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
        return $this->repos->save($betLine);*/
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

    public function convertBetType( $betType )
    {
        if( $betType === BetLineBase::_MATCH_ODDS ) {
            return 'MATCH_ODDS';
        }
        throw new \Exception("unknown bettype", E_ERROR);
    }

    protected function saveLayBacks(
        \DateTimeImmutable $dateTime,
        BetLineBase $betLine,
        $layBacks, $layBack
    ) {
        throw new \Exception("bookmaker should be added to layback", E_ERROR );
        $bookMaker = null;
        foreach( $layBacks as $layBackIt ){
            $layBackNew = new LayBack( $dateTime, $betLine, $bookMaker, $this->externalSystemBase );
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

    protected function getMarkets( $eventId, $betType )
    {
        return PeterColesBetfair::betting('listMarketCatalogue', [
            'filter' => [
                'eventIds' => [$eventId],
                'marketTypeCodes' => [$this->convertBetType( $betType )]
            ],
            'maxResults' => 3,
            'marketProjection' => ['RUNNER_METADATA']
        ]);
    }

    protected function getMarketBooks( $marketId ) {
        return PeterColesBetfair::betting('listMarketBook', [
            'marketIds' => [$marketId],
            // 'selectionId' => $runnerId,
            "priceProjection" => ["priceData" => ["EX_BEST_OFFERS"]],
            "orderProjection" => "ALL",
            "matchProjection" => "ROLLED_UP_BY_PRICE"
        ]);
    }

    protected function getCompetitorFromExternalId( $externalId )
    {
        $competitor = $this->externalCompetitorRepos->findImportable( $this->externalSystemBase, $externalId );
        if( $competitor === null ) {
            $this->logger->addNotice("competitor not found for externalid " . $externalId . " and externalSystem " . $this->externalSystemBase->getName() );
        }
        return $competitor;
    }

    protected function getGame( League $league, \DateTimeImmutable $startDateTime, $runners )
    {
        $competition = $this->competitionRepos->findOneByLeagueAndDate( $league,  $startDateTime );

        if( $competition === null ) {
            $this->logger->addNotice("competition not found for league " . $league->getName() . " and date " . $startDateTime->format(\DATE_ISO8601));
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

        $states = Game::STATE_CREATED + Game::STATE_INPLAY;
        $games = $this->gameRepos->findByExt( $homeCompetitor, $awayCompetitor, $competition, $states );
        if( $games === null ) {
            $this->logger->addNotice("game not found for homecompetitor " . $homeCompetitor->getName() . ",awaycompetitor" . $awayCompetitor->getName() . ", competition " . $competition->getName() . " and states " . $states );
        }
        return reset( $games );
    }
}