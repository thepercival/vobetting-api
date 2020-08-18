<?php
/**
 * Created by PhpStorm.
 * User: coen
 * Date: 6-3-18
 * Time: 19:55
 */

namespace VOBetting\ExternalSource\TheOddsApi\Helper;

use VOBetting\ExternalSource\TheOddsApi\Helper as TheOddsApiHelper;
use VOBetting\ExternalSource\TheOddsApi\ApiHelper as TheOddsApiApiHelper;
use Sports\ExternalSource\Association as ExternalSourceAssociation;
use Sports\Association as AssociationBase;
use VOBetting\ExternalSource\TheOddsApi;
use Psr\Log\LoggerInterface;
use stdClass;
use Sports\ExternalSource\SofaScore;
use Sports\Import\Service as ImportService;

class Association extends TheOddsApiHelper implements ExternalSourceAssociation
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
        TheOddsApi $parent,
        TheOddsApiApiHelper $apiHelper,
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
        return $this->apiHelper->getLeagues();
    }

    /**
     *
     *
     * @param array|stdClass[] $externalLeagues
     */
    protected function setAssociations(array $externalLeagues)
    {
        $defaultAssociation = $this->getDefaultAssociation();
        $this->associations = [ $defaultAssociation->getId() => $defaultAssociation ];

        /** @var stdClass $externalLeague */
        foreach ($externalLeagues as $externalLeague) {

            $name = $this->apiHelper->getAssociationName( $externalLeague );
            if ($this->hasName($this->associations, $name)) {
                continue;
            }
            $association = $this->createAssociation($externalLeague) ;
            $this->associations[$association->getId()] = $association;
        }
    }

    protected function createAssociation(stdClass $externalLeague): AssociationBase
    {
        $association = new AssociationBase($this->apiHelper->getAssociationName( $externalLeague ));
        $association->setParent($this->getDefaultAssociation());
        $association->setId($this->apiHelper->getAssociationId( $externalLeague ));
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
