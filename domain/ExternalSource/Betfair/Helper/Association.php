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
use Voetbal\ExternalSource\Association as ExternalSourceAssociation;
use Voetbal\Association as AssociationBase;
use VOBetting\ExternalSource\Betfair;
use Psr\Log\LoggerInterface;
use stdClass;
use Voetbal\ExternalSource\SofaScore;
use Voetbal\Import\Service as ImportService;

class Association extends BetfairHelper implements ExternalSourceAssociation
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

    public function getAssociations(): array
    {
        $this->initAssociations();
        return array_values( $this->associations );
    }

    protected function initAssociations()
    {
        if( $this->associations !== null ) {
            return;
        }
        $this->setAssociations( $this->getAssociationData() );
    }

    public function getAssociation( $id = null ): ?AssociationBase
    {
        $this->initAssociations();
        if( array_key_exists( $id, $this->associations ) ) {
            return $this->associations[$id];
        }
        return null;
    }

    /**
     * @return array|stdClass[]
     */
    protected function getAssociationData(): array
    {
        return $this->apiHelper->listLeagues( [] );
    }

    /**
     *
     *
     * @param array|stdClass[] $externalAssociations
     */
    protected function setAssociations(array $externalAssociations)
    {
        $defaultAssociation = $this->getDefaultAssociation();
        $this->associations = [ $defaultAssociation->getId() => $defaultAssociation ];

        /** @var stdClass $externalAssociation */
        foreach ($externalAssociations as $externalAssociation) {
            if( !property_exists( $externalAssociation, "competitionRegion") ) {
                continue;
            }

            $name = $externalAssociation->competitionRegion;
            if( $this->hasName( $this->associations, $name ) ) {
                continue;
            }
            $association = $this->createAssociation( $externalAssociation ) ;
            $this->associations[$association->getId()] = $association;
        }
    }

    protected function createAssociation( stdClass $externalAssociation ): AssociationBase
    {
        $association = new AssociationBase($externalAssociation->competitionRegion);
        $association->setParent( $this->getDefaultAssociation() );
        $association->setId($externalAssociation->competitionRegion);
        return $association;
    }

    protected function getDefaultAssociation(): AssociationBase {
        if( $this->defaultAssociation === null ) {
            $this->defaultAssociation = new AssociationBase("EARTH" );
            $this->defaultAssociation->setId("EARTH");
        }
        return $this->defaultAssociation;
    }
}