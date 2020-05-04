<?php
/**
 * Created by PhpStorm.
 * User: coen
 * Date: 6-3-18
 * Time: 19:55
 */

namespace VOBetting\ExternalSource\Matchbook\Helper;

use VOBetting\ExternalSource\Matchbook\Helper as MatchbookHelper;
use VOBetting\ExternalSource\Matchbook\ApiHelper as MatchbookApiHelper;
use Voetbal\ExternalSource\Association as ExternalSourceAssociation;
use Voetbal\Association as AssociationBase;
use VOBetting\ExternalSource\Matchbook;
use Psr\Log\LoggerInterface;
use stdClass;
use Voetbal\ExternalSource\SofaScore;
use Voetbal\Import\Service as ImportService;

class Association extends MatchbookHelper implements ExternalSourceAssociation
{
    /**
     * @var array|AssociationBase[]|null
     */
    protected $associations;
    /**
     * @var AssociationBase
     */
    protected $defaultAssociation;

    public function __construct(
        Matchbook $parent,
        MatchbookApiHelper $apiHelper,
        LoggerInterface $logger
    ) {
        parent::__construct(
            $parent,
            $apiHelper,
            $logger
        );
    }

    public function getAssociations(): array
    {
        $this->initAssociations();
        return array_values($this->associations);
    }

    protected function initAssociations()
    {
        if ($this->associations !== null) {
            return;
        }
        $this->setAssociations($this->getAssociationData());
    }

    public function getAssociation($id = null): ?AssociationBase
    {
        $this->initAssociations();
        if (array_key_exists($id, $this->associations)) {
            return $this->associations[$id];
        }
        return null;
    }

    /**
     * @return array|stdClass[]
     */
    protected function getAssociationData(): array
    {
        return $this->apiHelper->getEventsBySport();
    }

    /**
     *
     *
     * @param array|stdClass[] $externalEvents
     */
    protected function setAssociations(array $externalEvents)
    {
        $defaultAssociation = $this->getDefaultAssociation();
        $this->associations = [ $defaultAssociation->getId() => $defaultAssociation ];

        /** @var stdClass $externalEvent */
        foreach ($externalEvents as $externalEvent) {

            $externalAssociation = $this->apiHelper->getAssociationData( $externalEvent->{"meta-tags"} );
            if( $externalAssociation === null ) {
                continue;
            }
            $name = $externalAssociation->name;
            if ($this->hasName($this->associations, $name)) {
                continue;
            }
            $association = $this->createAssociation($externalAssociation) ;
            $this->associations[$association->getId()] = $association;
        }
    }

    protected function createAssociation(stdClass $externalAssociation): AssociationBase
    {
        $association = new AssociationBase($externalAssociation->name);
        $association->setParent($this->getDefaultAssociation());
        $association->setId($externalAssociation->{"url-name"});
        return $association;
    }

    protected function getDefaultAssociation(): AssociationBase
    {
        if ($this->defaultAssociation === null) {
            $this->defaultAssociation = new AssociationBase("EARTH");
            $this->defaultAssociation->setId("EARTH");
        }
        return $this->defaultAssociation;
    }
}
