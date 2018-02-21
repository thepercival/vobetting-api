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

    public function __construct(
        ExternalSystemBase $externalSystem,
        CompetitionseasonRepos $competitionseasonRepos,
        ExternalTeamRepos $externalTeamRepos,
        GameRepos $gameRepos,
        BetLineRepos $betLineRepos, LayBackRepos $layBackRepos
    )
    {
        $this->setExternalSystem( $externalSystem );
        $this->competitionseasonRepos = $competitionseasonRepos;
        $this->externalTeamRepos = $externalTeamRepos;
        $this->gameRepos = $gameRepos;
        $this->betLineRepos = $betLineRepos;
        $this->layBackRepos = $layBackRepos;
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

    public function getGame( Competition $competition, \DateTimeImmutable $startDateTime, $runners )
    {
        $competitionseason = $this->competitionseasonRepos->findOneByCompetitionAndDate( $competition,  $startDateTime );

        if( $competitionseason === null ) {
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
            // var_dump($runner->runnerName . " : " . $runner->metadata->runnerId);
        }

        $homeTeam = $this->getTeamFromExternalId( $homeRunnerId );
        $awayTeam = $this->getTeamFromExternalId( $awayRunnerId );
        if( $homeTeam === null or $awayTeam === null  ) {
            return null;
        }

        $game = $this->gameRepos->findByExt(
            $homeTeam,
            $awayTeam,
            $competitionseason,
            Game::STATE_CREATED + Game::STATE_INPLAY
        );
        return $game;
    }

    public function getTeamFromExternalId( $externalId )
    {
        return $this->externalTeamRepos->findImportableBy( $this->externalSystem, $externalId );
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

    public function getEvents( ExternalObject $externalObject )
    {
        return PeterColesBetfair::betting('listEvents',
            ['filter' => [
                'competitionIds' => [$externalObject->getExternalId()]
            ]]);
    }

    public function getMarkets( $eventId, $betType )
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

    public function getBetLines( $marketId, $runnerId ) {
        return PeterColesBetfair::betting('listRunnerBook', [
            'marketId' => $marketId,
            'selectionId' => $runnerId,
            "priceProjection" => ["priceData" => ["EX_BEST_OFFERS"]],
            "orderProjection" => "ALL",
            "matchProjection" => "ROLLED_UP_BY_PRICE"
        ]);
    }

    public function convertBetType( $betType )
    {
        if( $betType === BetLine::_MATCH_ODDS ) {
            return 'MATCH_ODDS';
        }
        throw new \Exception("unknown bettype", E_ERROR);
    }

    public function processEvent( $competition, $event, $betType ) {
        $markets = $this->getMarkets( $event->event->id, $betType );

        $startDateTime = \DateTimeImmutable::createFromFormat('Y-m-d\TH:i:s.u\Z', $event->event->openDate);
        $dateTime = new \DateTimeImmutable();

        foreach ($markets as $market) {
            $game = $this->getGame($competition, $startDateTime, $market->runners);
            if ( $game === null ) {
                var_dump("gameis null");
                continue;
            }

            foreach ($market->runners as $runner) {
                // use $runner->selectionId as marketbook
                //  var_dump($runner->runnerName . " : " . $runner->metadata->runnerId);
                //  var_dump($runner->sortPriority);

                $betLines = $this->getBetLines($market->marketId, $runner->metadata->runnerId);
                foreach ($betLines as $betLine) {
                    $team = $this->getTeamFromExternalId($runner->metadata->runnerId);
                    $poulePlace = $game->getPoulePlaceForTeam($team);

                    var_dump($betLine->status); // IF CLOSED => UPDATE GAME!!
                    $runnerOne = $betLine->runners[0];
                    var_dump($runnerOne->status); // "ACTIVE"

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

                    // save these!!!
                    $backs = $runnerOne->ex->availableToBack;
                    $lays = $runnerOne->ex->availableToLay;
                    $this->saveLayBacks( $dateTime, $betLine, $backs, true );
                    $this->saveLayBacks( $dateTime, $betLine, $lays, false );
                    break;
                }
                break;
            }
        }
    }

    protected function saveLayBacks(
        \DateTimeImmutable $dateTime,
        BetLine $betLine,
        $layBacks, $layBack
    ) {
        foreach( $layBacks as $layBackIt ){
            var_dump( ( $layBack ? "back " : "lay " ) . $layBackIt->price . " -> " . $layBackIt->size);
            $layBackNew = new LayBack( $dateTime, $betLine, $this->externalSystem );
            $layBackNew->setBack( $layBack );
            $layBackNew->setPrice( $layBackIt->price );
            $layBackNew->setSize( $layBackIt->size );
            $this->layBackRepos->save($layBackNew);

        }
    }
}