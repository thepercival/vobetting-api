<?php
/**
 * Created by PhpStorm.
 * User: coen
 * Date: 12-4-19
 * Time: 19:16
 */

namespace App;

use Monolog\Logger;
use Voetbal\External\System\Logger\GameLogger as GameLoggerInterface;
use Voetbal\Competition;
use Voetbal\External\System as ExternalSystem ;
use Voetbal\Game;

class GameLogger extends Logger implements GameLoggerInterface
{
    /**
     * @var string
     */
    private $url;

    public function __construct(string $name, string $url )
    {
        $this->url = $url;
        parent::__construct( $name );
    }

    public function addGameNotFoundNotice( string $msg, Competition $competition )
    {
        $this->addNotice( $msg . ', check ' . $this->url . '/admin/games/' . $competition->getId() );
    }

    public function addExternalGameNotFoundNotice( string $msg, ExternalSystem $externalSystem, Game $game, Competition $competition )
    {
        $this->addNotice( $msg . ', check ?'); // . $this->url . 'admin/games/' . $competition->getId();
    }

    public function addExternalCompetitorNotFoundNotice( string $msg, ExternalSystem $externalSystem, string $externalSystemCompetitor )
    {
        $this->addNotice( $msg . ', check ?'); // . $this->url . 'admin/games/' . $competition->getId();
    }
}
