<?php
/**
 * Created by PhpStorm.
 * User: coen
 * Date: 14-3-18
 * Time: 11:58
 */

namespace VOBetting\ExternalSource\Importable;

use VOBetting\BetLine\Repository as BetLineRepos;
use VOBetting\External\System\Importer\BetLine as BetLineImporter;
use Sports\Competition\Repository as CompetitionRepos;
use Sports\Game\Repository as GameRepos;
use Sports\External\Competitor\Repository as ExternalCompetitorRepos;
use VOBetting\LayBack\Repository as LayBackRepos;
use VOBetting\Bookmaker\Repository as BookmakerRepos;
use Monolog\Logger;

interface BetLine
{
//    public function getBetLineImporter(
//        BetLineRepos $repos,
//        CompetitionRepos $competitionRepos,
//        GameRepos $gameRepos,
//        ExternalCompetitorRepos $externalCompetitorRepos,
//        LayBackRepos $layBackRepos,
//        BookmakerRepos $bookmakerRepos,
//        Logger $logger
//    ) : BetLineImporter;
}
