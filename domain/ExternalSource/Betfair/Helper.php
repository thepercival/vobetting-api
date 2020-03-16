<?php
/**
 * Created by PhpStorm.
 * User: coen
 * Date: 6-3-18
 * Time: 19:55
 */

namespace VOBetting\ExternalSource\Betfair;

use VOBetting\ExternalSource\Betfair;
use Psr\Log\LoggerInterface;

class Helper
{
    /**
     * @var Betfair
     */
    protected $parent;
    /**
     * @var ApiHelper
     */
    protected $apiHelper;
    /**
     * @var LoggerInterface;
     */
    protected $logger;
    

    public function __construct(
        Betfair $parent,
        ApiHelper $apiHelper,
        LoggerInterface $logger
    )
    {
        $this->parent = $parent;
        $this->apiHelper = $apiHelper;
        $this->logger = $logger;
    }

    protected function hasName( array $objects, string $name ): bool {
        foreach( $objects as $object ) {
            if( $object->getName() === $name ) {
                return true;
            }
        }
        return false;
    }

    private function notice( $msg ) {
        $this->logger->notice( $this->parent->getExternalSource()->getName() . " : " . $msg );
    }

    private function error( $msg ) {
        $this->logger->error( $this->parent->getExternalSource()->getName() . " : " . $msg );
    }
}