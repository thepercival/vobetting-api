<?php
/**
 * Created by PhpStorm.
 * User: coen
 * Date: 6-3-18
 * Time: 19:55
 */

namespace VOBetting\ExternalSource\TheOddsApi;

use VOBetting\ExternalSource\TheOddsApi;
use Psr\Log\LoggerInterface;
use Sports\Competition;
use Sports\Competitor;
use Sports\Poule;
use SportsHelpers\Range as VoetbalRange;
use Sports\Structure as StructureBase;
use Sports\Structure\Service as StructureService;
use Sports\Structure\Options as StructureOptions;

class Helper
{
    /**
     * @var TheOddsApi
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
        TheOddsApi $parent,
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
