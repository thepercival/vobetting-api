<?php
/**
 * Created by PhpStorm.
 * User: coen
 * Date: 6-3-18
 * Time: 19:55
 */

namespace VOBetting\ExternalSource\Betfair\Helper;

use VOBetting\ExternalSource\Betfair\Helper as BetfairHelper;
use VOBetting\ExternalSource\Betfair\ApiHelper as BetfairApiHelper;
use VOBetting\ExternalSource\Bookmaker as ExternalSourceBookmaker;
use VOBetting\Bookmaker as BookmakerBase;
use VOBetting\ExternalSource\Betfair;
use Psr\Log\LoggerInterface;
use stdClass;

class Bookmaker extends BetfairHelper implements ExternalSourceBookmaker
{
    /**
     * @var array|BookmakerBase[]|null
     */
    protected $bookmakers;

    public function __construct(
        Betfair $parent,
        BetfairApiHelper $apiHelper,
        LoggerInterface $logger
    ) {
        parent::__construct(
            $parent,
            $apiHelper,
            $logger
        );
    }

    public function getBookmakers(): array
    {
        $this->initBookmakers();
        return array_values($this->bookmakers);
    }

    protected function initBookmakers()
    {
        if ($this->bookmakers !== null) {
            return;
        }
        $this->setBookmakers($this->getBookmakerData());
    }

    public function getBookmaker($id = null): ?BookmakerBase
    {
        $this->initBookmakers();
        if (array_key_exists($id, $this->bookmakers)) {
            return $this->bookmakers[$id];
        }
        return null;
    }

    /**
     * @return array|stdClass[]
     */
    protected function getBookmakerData(): array
    {
        $class = new stdClass();
        $class->id = $this->parent::NAME;
        return [ $class ];
    }

    /**
     *
     *
     * @param array|stdClass[] $externalBookmakers
     */
    protected function setBookmakers(array $externalBookmakers)
    {
        $this->bookmakers = [];

        /** @var stdClass $externalBookmaker */
        foreach ($externalBookmakers as $externalBookmaker) {
            $name = $externalBookmaker->id;
            if ($this->hasName($this->bookmakers, $name)) {
                continue;
            }
            $bookmaker = $this->createBookmaker($externalBookmaker) ;
            $this->bookmakers[$bookmaker->getId()] = $bookmaker;
        }
    }

    protected function createBookmaker(stdClass $externalBookmaker): BookmakerBase
    {
        $bookmaker = new BookmakerBase($externalBookmaker->id, true);
        $bookmaker->setId($externalBookmaker->id);
        return $bookmaker;
    }
}
