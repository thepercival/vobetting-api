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
use PeterColes\Betfair\Betfair as PeterColesBetfair;
use League\Period\Period;
use VOBetting\BetLine as BetLineBase;
use Voetbal\Competition\Repository as CompetitionRepos;
use Voetbal\Game\Repository as GameRepos;
use Voetbal\External\Team\Repository as ExternalTeamRepos;
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
     * @var ExternalTeamRepos
     */
    private $externalTeamRepos;
    /**
     * @var BetLineRepos
     */
    private $betLineRepos;
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
        ExternalTeamRepos $externalTeamRepos,
        LayBackRepos $layBackRepos,
        Logger $logger

    ) {
        $this->externalSystemBase = $externalSystemBase;
        $this->apiHelper = $apiHelper;
        $this->repos = $repos;
        $this->competitionRepos = $competitionRepos;
        $this->gameRepos = $gameRepos;
        $this->externalTeamRepos = $externalTeamRepos;
        $this->layBackRepos = $layBackRepos;
        $this->logger = $logger;
    }

    public function get( ExternalLeague $externalLeague )
    {
        return PeterColesBetfair::betting('listEvents',
            ['filter' => [
                'competitionIds' => [$externalLeague->getExternalId()]
                ,"marketStartTime" => [
                    "from" => $this->getImportPeriod()->getStartDate()->format($this->apiHelper->getDateFormat()),
                    "to" => $this->getImportPeriod()->getEndDate()->format($this->apiHelper->getDateFormat())]
            ]
            ]);
    }

    public function getId( $externalSystemBetLine )
    {
        throw new \Exception("notimplyet", E_ERROR );
    }

    public function process( League $league, $externalSystemEvent, $betType ) {
        $markets = $this->getMarkets( $externalSystemEvent->event->id, $betType );
        $startDateTime = \DateTimeImmutable::createFromFormat('Y-m-d\TH:i:s.u\Z', $externalSystemEvent->event->openDate);

        foreach ($markets as $market) {
            $game = $this->getGame($league, $startDateTime, $market->runners);
            if ( $game === null ) {
                continue;
            }

            $marketBooks = $this->getMarketBooks($market->marketId);
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
        if( $runner->selectionId !== ExternalSystemBetfair::THE_DRAW ) { // the draw
            $team = $this->getTeamFromExternalId($runner->selectionId);
            if( $team === null ) {
                return null;
            }
            $poulePlace = $game->getPoulePlaceForTeam($team);
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
        foreach( $layBacks as $layBackIt ){
            $layBackNew = new LayBack( $dateTime, $betLine, $this->externalSystemBase );
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

    protected function getTeamFromExternalId( $externalId )
    {
        $team = $this->externalTeamRepos->findImportable( $this->externalSystemBase, $externalId );
        if( $team === null ) {
            $this->logger->addNotice("team not found for externalid " . $externalId . " and externalSystem " . $this->externalSystemBase->getName() );
        }
        return $team;
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

        $homeTeam = $this->getTeamFromExternalId( $homeRunnerId );
        if( $homeTeam === null ) {
            return null;
        }
        $awayTeam = $this->getTeamFromExternalId( $awayRunnerId );
        if( $awayTeam === null  ) {
            return null;
        }

        $states = Game::STATE_CREATED + Game::STATE_INPLAY;
        $games = $this->gameRepos->findByExt( $homeTeam, $awayTeam, $competition, $states );
        if( $games === null ) {
            $this->logger->addNotice("game not found for hometeam " . $homeTeam->getName() . ",awayteam " . $awayTeam->getName() . ", competition " . $competition->getName() . " and states " . $states );
        }
        return reset( $games );
    }
}