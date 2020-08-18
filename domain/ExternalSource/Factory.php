<?php
/**
 * Created by PhpStorm.
 * User: coen
 * Date: 14-3-18
 * Time: 19:40
 */

namespace VOBetting\ExternalSource;

use Psr\Log\LoggerInterface;
use Sports\CacheItemDb\Repository as CacheItemDbRepository;
use Sports\ExternalSource;
use VOBetting\ExternalSource\Bookmaker as ExternalSourceBookmaker;
use VOBetting\ExternalSource\LayBack as ExternalSourceLayBack;
use Sports\ExternalSource\Factory as ExternalSourceFactory;
use Sports\ExternalSource\Repository;
use SportsImport\ExternalSource\Implementation as ExternalSourceImplementation;

class Factory extends ExternalSourceFactory
{
    protected const BOOKMAKER = 512;
    protected const LAYBACK = 1024;

    public function __construct(
        Repository $externalSourceRepos,
        CacheItemDbRepository $cacheItemDbRepos,
        LoggerInterface $logger
    ) {
        parent::__construct($externalSourceRepos, $cacheItemDbRepos, $logger);
    }

    protected function create(ExternalSource $externalSource): ExternalSourceImplementation
    {
        if ($externalSource->getName() === Betfair::NAME) {
            return new Betfair($externalSource, $this->cacheItemDbRepos, $this->logger);
        } elseif ($externalSource->getName() === Matchbook::NAME) {
            return new Matchbook($externalSource, $this->cacheItemDbRepos, $this->logger);
        } elseif ($externalSource->getName() === TheOddsApi::NAME) {
            return new TheOddsApi($externalSource, $this->cacheItemDbRepos, $this->logger);
        }
        return parent::create($externalSource);
    }

    protected function getImplementations(ExternalSource\Implementation $implementation)
    {
        $implementations = parent::getImplementations($implementation);
        if ($implementation instanceof ExternalSourceBookmaker) {
            $implementations += static::BOOKMAKER;
        }
        if ($implementation instanceof ExternalSourceLayBack) {
            $implementations += static::LAYBACK;
        }
        return $implementations;
    }
}
