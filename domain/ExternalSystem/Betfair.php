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
use VOBetting\ExternalSystem as ExternalSystemInterface;
use Voetbal\External\Object as ExternalObject;
use Voetbal\Competitionseason\Repository as CompetitionseasonRepos;
use Voetbal\Competition;
use PeterColes\Betfair\Betfair as PeterColesBetfair;
use Voetbal\Game;
use Voetbal\External\Team\Repository as ExternalTeamRepos;
use Voetbal\Game\Repository as GameRepos;
use VOBetting\BetLine\Repository as BetLineRepos;
use VOBetting\LayBack\Repository as LayBackRepos;
use VOBetting\BetLine;
use VOBetting\LayBack;
use Monolog\Logger;

class Betfair implements ExternalSystemInterface
{
    /**
     * @var ExternalSystem
     */
    private $externalSystem;

    /**
     * @var CompetitionseasonRepos
     */
    private $competitionseasonRepos;

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

    public function __construct(
        ExternalSystemBase $externalSystem,
        CompetitionseasonRepos $competitionseasonRepos,
        ExternalTeamRepos $externalTeamRepos,
        GameRepos $gameRepos,
        BetLineRepos $betLineRepos, LayBackRepos $layBackRepos,
        Logger $logger
    )
    {
        $this->setExternalSystem( $externalSystem );
        $this->competitionseasonRepos = $competitionseasonRepos;
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
            ]]);
    }

    public function processEvent( Competition $competition, $event, $betType ) {
        $markets = $this->getMarkets( $event->event->id, $betType );

        $startDateTime = \DateTimeImmutable::createFromFormat('Y-m-d\TH:i:s.u\Z', $event->event->openDate);
        $dateTime = new \DateTimeImmutable();

        foreach ($markets as $market) {
            $game = $this->getGame($competition, $startDateTime, $market->runners);
            if ( $game === null ) {
                continue;
            }
            $game = $this->syncStartDateTime( $game, $startDateTime);

            foreach ($market->runners as $runner) {
                // use $runner->selectionId as marketbook
                //  var_dump($runner->runnerName . " : " . $runner->metadata->runnerId);
                //  var_dump($runner->sortPriority);
                // var_dump($runner->metadata->runnerId); die();
                $poulePlace = null;
                if( $runner->metadata->runnerId !== "58805" ) { // the draw
                    $team = $this->getTeamFromExternalId($runner->metadata->runnerId);
                    if( $team === null ) {
                        continue;
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
                $this->betLineRepos->save($betLine);

                $marketBooks = $this->getMarketBooks($market->marketId, $runner->metadata->runnerId);
                foreach ($marketBooks as $marketBook) {

                    // var_dump($betLine->status); // IF CLOSED => UPDATE GAME!!
                    $runnerOne = $marketBook->runners[0];
                    // var_dump($runnerOne->status); // "ACTIVE"
                    $backs = $runnerOne->ex->availableToBack;
                    $lays = $runnerOne->ex->availableToLay;
                    $this->saveLayBacks( $dateTime, $betLine, $backs, true );
                    $this->saveLayBacks( $dateTime, $betLine, $lays, false );
                    break; // should not be necessary
                }
                // break;
            }
        }
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

    protected function getMarketBooks( $marketId, $runnerId ) {
        return PeterColesBetfair::betting('listRunnerBook', [
            'marketId' => $marketId,
            'selectionId' => $runnerId,
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

    protected function getGame( Competition $competition, \DateTimeImmutable $startDateTime, $runners )
    {
        $competitionseason = $this->competitionseasonRepos->findOneByCompetitionAndDate( $competition,  $startDateTime );

        if( $competitionseason === null ) {
            $this->logger->addNotice("competitionseason not found for competition " . $competition->getName() . " and date " . $startDateTime->format(\DATE_ISO8601));
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
        $game = $this->gameRepos->findByExt( $homeTeam, $awayTeam, $competitionseason, $states );
        if( $game === null ) {
            $this->logger->addNotice("game not found for hometeam " . $homeTeam->getName() . ",awayteam " . $awayTeam->getName() . ", competitionseason " . $competitionseason->getName() . " and states " . $states );
        }
        return $game;
    }
}