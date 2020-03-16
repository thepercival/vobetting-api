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
use Voetbal\ExternalSource\Factory as ExternalSourceFactory;
use Voetbal\ExternalSource\Repository;

class Factory extends ExternalSourceFactory
{
    /**
     * @var Repository
     */
    private $externalSourceRepos;
    /**
     * @var CacheItemDbRepository
     */
    private $cacheItemDbRepos;
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        Repository $externalSourceRepos,
        CacheItemDbRepository $cacheItemDbRepos,
        LoggerInterface $logger
    )
    {
        parent::__construct( $externalSourceRepos, $cacheItemDbRepos, $logger );
    }

    public function create( ExternalSource $externalSource ) {
        if( $externalSource->getName() === "betfair" ) {
            return new Betfair($externalSource);
        }
//        if( $externalSystem->getName() === "API Football" ) {
//            return new APIFootball($externalSystem);
//        }
        return parent::create($externalSource);
    }
}
