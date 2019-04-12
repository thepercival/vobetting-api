<?php
/**
 * Created by PhpStorm.
 * User: coen
 * Date: 14-3-18
 * Time: 11:58
 */

namespace VOBetting\External\System\Importable;

use VOBetting\BetLine\Repository as BetLineRepos;
use VOBetting\External\System\Importer\BetLine as BetLineImporter;
use Voetbal\Competition\Repository as CompetitionRepos;
use Voetbal\Game\Repository as GameRepos;
use Voetbal\External\Competitor\Repository as ExternalCompetitorRepos;
use VOBetting\LayBack\Repository as LayBackRepos;
use Monolog\Logger;

interface BetLine
{
    public function getBetLineImporter(
        BetLineRepos $repos,
        CompetitionRepos $competitionRepos,
        GameRepos $gameRepos,
        ExternalCompetitorRepos $externalCompetitorRepos,
        LayBackRepos $layBackRepos,
        Logger $logger
    ) : BetLineImporter;
}