<?php
/**
 * Created by PhpStorm.
 * User: coen
 * Date: 21-2-18
 * Time: 10:42
 */

namespace VOBetting\ExternalSystem;

use VOBetting\ExternalSystem;
use Voetbal\External\System as ExternalSystemBase;
use Voetbal\External\System\Def as ExternalSystemInterface;
use VOBetting\ExternalSystem as VOBettingExternalSystemInterface;
use Voetbal\External\Object as ExternalObject;
use Voetbal\Competition\Repository as CompetitionRepos;
use Voetbal\League;
use PeterColes\Betfair\Betfair as PeterColesBetfair;
use Voetbal\Game;
use Voetbal\External\Team\Repository as ExternalTeamRepos;
use Voetbal\Game\Repository as GameRepos;
use VOBetting\BetLine\Repository as BetLineRepos;
use VOBetting\LayBack\Repository as LayBackRepos;
use VOBetting\BetLine;
use VOBetting\LayBack;
use Monolog\Logger;
use League\Period\Period;


class Betfair implements ExternalSystemInterface, VOBettingExternalSystemInterface
{
    /**
     * @var ExternalSystem
     */
    private $externalSystem;

    /**
     * @var CompetitionRepos
     */
    private $competitionRepos;

    /**
     * @var ExternalTeamRepos
     */
    private $externalTeamRepos;

    /**
     * @var GameRepos
     */
    private $gameRepos;

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

    CONST DATE_FORMAT = 'Y-m-d\TH:i:s\Z';
    CONST THE_DRAW = "58805";

    public function __construct(
        ExternalSystemBase $externalSystem,
        CompetitionRepos $competitionRepos,
        ExternalTeamRepos $externalTeamRepos,
        GameRepos $gameRepos,
        BetLineRepos $betLineRepos, LayBackRepos $layBackRepos,
        Logger $logger
    )
    {
        $this->setExternalSystem( $externalSystem );
        $this->competitionRepos = $competitionRepos;
        $this->externalTeamRepos = $externalTeamRepos;
        $this->gameRepos = $gameRepos;
        $this->betLineRepos = $betLineRepos;
        $this->layBackRepos = $layBackRepos;
        $this->logger = $logger;
    }

    public function init() {

        PeterColesBetfair::init(
            $this->externalSystem->getApikey(),
            $this->externalSystem->getUsername(),
            $this->externalSystem->getPassword()
        );
    }

    public function setMaxDaysBeforeImport( $maxDaysBeforeImport ) {
        $this->maxDaysBeforeImport = $maxDaysBeforeImport;
    }

    public function getImportPeriod() {
        if( $this->period === null ) {
            $now = new \DateTimeImmutable();
            $this->period = new Period( $now, $now->modify("+".$this->maxDaysBeforeImport." days") );
        }
        return $this->period;
    }

    /**
     * @return ExternalSystemBase
     */
    public function getExternalSystem()
    {
        return $this->externalSystem;
    }

    /**
     * @param ExternalSystemBase $externalSystem
     */
    public function setExternalSystem( ExternalSystemBase $externalSystem )
    {
        $this->externalSystem = $externalSystem;
    }

    public function getEvents( ExternalObject $externalObject )
    {
        return PeterColesBetfair::betting('listEvents',
            ['filter' => [
                'competitionIds' => [$externalObject->getExternalId()]
                ,"marketStartTime" => [
                    "from" => $this->getImportPeriod()->getStartDate()->format(static::DATE_FORMAT),
                    "to" => $this->getImportPeriod()->getEndDate()->format(static::DATE_FORMAT)]
                ]
            ]);
    }

    public function processEvent( League $league, $event, $betType ) {
        $markets = $this->getMarkets( $event->event->id, $betType );
        $startDateTime = \DateTimeImmutable::createFromFormat('Y-m-d\TH:i:s.u\Z', $event->event->openDate);

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

    protected function syncBetLine( Game $game, $betType, $runner)
    {
        $poulePlace = null;
        if( $runner->selectionId !== static::THE_DRAW ) { // the draw
        $team = $this->getTeamFromExternalId($runner->selectionId);
        if( $team === null ) {
            return null;
        }
        $poulePlace = $game->getPoulePlaceForTeam($team);
        }
        $betLine = $this->betLineRepos->findOneBy(array(
            "game" => $game,
            "betType" => $betType,
            "poulePlace" => $poulePlace
        ));
        if( $betLine === null ) {
            $betLine = new BetLine($game, $betType);
            $betLine->setPoulePlace($poulePlace);
        }
        // maybe save close state here
        return $this->betLineRepos->save($betLine);
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
        if( $betType === BetLine::_MATCH_ODDS ) {
            return 'MATCH_ODDS';
        }
        throw new \Exception("unknown bettype", E_ERROR);
    }

    protected function saveLayBacks(
        \DateTimeImmutable $dateTime,
        BetLine $betLine,
        $layBacks, $layBack
    ) {
        foreach( $layBacks as $layBackIt ){
            $layBackNew = new LayBack( $dateTime, $betLine, $this->externalSystem );
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
        $team = $this->externalTeamRepos->findImportableBy( $this->externalSystem, $externalId );
        if( $team === null ) {
            $this->logger->addNotice("team not found for externalid " . $externalId . " and externalSystem " . $this->externalSystem->getName() );
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
        $game = $this->gameRepos->findByExt( $homeTeam, $awayTeam, $competition, $states );
        if( $game === null ) {
            $this->logger->addNotice("game not found for hometeam " . $homeTeam->getName() . ",awayteam " . $awayTeam->getName() . ", competition " . $competition->getName() . " and states " . $states );
        }
        return $game;
    }
}