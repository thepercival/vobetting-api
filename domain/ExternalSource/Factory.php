<?php
/**
 * Created by PhpStorm.
 * User: coen
 * Date: 14-3-18
 * Time: 19:40
 */

namespace VOBetting\ExternalSource;

use Psr\Log\LoggerInterface;
use Voetbal\CacheItemDb\Repository as CacheItemDbRepository;
use Voetbal\ExternalSource;
use VOBetting\ExternalSource\Bookmaker as ExternalSourceBookmaker;
use Voetbal\ExternalSource\Factory as ExternalSourceFactory;
use Voetbal\ExternalSource\Repository;

class Factory extends ExternalSourceFactory
{
    protected const BOOKMAKER = 512;

    public function __construct(
        Repository $externalSourceRepos,
        CacheItemDbRepository $cacheItemDbRepos,
        LoggerInterface $logger
    ) {
        parent::__construct($externalSourceRepos, $cacheItemDbRepos, $logger);
    }

    protected function create(ExternalSource $externalSource)
    {
        if ($externalSource->getName() === Betfair::NAME) {
            return new Betfair($externalSource, $this->cacheItemDbRepos, $this->logger);
        }
        return parent::create($externalSource);
    }

    protected function getImplementations(ExternalSource\Implementation $implementation)
    {
        $implementations = parent::getImplementations($implementation);
        if ($implementation instanceof ExternalSourceBookmaker) {
            $implementations += static::BOOKMAKER;
        }
        return $implementations;
    }
}
