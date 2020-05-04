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
use Voetbal\Competition;
use Voetbal\Competitor;
use Voetbal\Poule;
use Voetbal\Range as VoetbalRange;
use Voetbal\Structure as StructureBase;
use Voetbal\Structure\Service as StructureService;
use Voetbal\Structure\Options as StructureOptions;

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
    ) {
        $this->parent = $parent;
        $this->apiHelper = $apiHelper;
        $this->logger = $logger;
    }

    protected function hasName(array $objects, string $name): bool
    {
        foreach ($objects as $object) {
            if ($object->getName() === $name) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param Competition $competition
     * @param array|Competitor[] $competitors
     * @return Poule
     */
    protected function createDummyPoule( Competition $competition, array $competitors ): Poule {
        $structureOptions = new StructureOptions(
            new VoetbalRange(1, 32),
            new VoetbalRange(2, 256),
            new VoetbalRange(2, 30)
        );
        $structureService = new StructureService($structureOptions);
        $structure = $structureService->create($competition, count($competitors), 1);
        $poule = $structure->getRootRound()->getPoule(1);
        foreach( $poule->getPlaces() as $place ) {
            $place->setCompetitor( array_shift($competitors) );
        }
        return $poule;
    }

    private function notice($msg)
    {
        $this->logger->notice($this->parent->getExternalSource()->getName() . " : " . $msg);
    }

    private function error($msg)
    {
        $this->logger->error($this->parent->getExternalSource()->getName() . " : " . $msg);
    }
}
