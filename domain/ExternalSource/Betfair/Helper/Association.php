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
        if( $this->associations !== null ) {
            return $this->associations;
        }
        $this->associations = [];

        $this->setAssociations( $this->apiHelper->listCountries( [] ) );
        $this->associations = array_values( $this->associations );
        return $this->associations;
    }

    public function getAssociation( $id = null ): ?AssociationBase
    {
        $associations = $this->getAssociations();
        if( array_key_exists( $id, $associations ) ) {
            return $associations[$id];
        }
        return null;
    }

    /**
     *
     *
     * @param array $countries |stdClass[]
     */
    protected function setAssociations(array $countries)
    {
        $defaultAssociation = $this->getDefaultAssociation();
        $this->associations = [ $defaultAssociation->getId() => $defaultAssociation ];

        /** @var \stdClass $country */
        foreach ($countries as $country) {
            if( $country->countryCode === null ) {
                continue;
            }
            $name = $country->countryCode;
            if( $this->hasName( $this->associations, $name ) ) {
                continue;
            }
            $association = $this->createAssociation( $country ) ;
            $this->associations[$association->getId()] = $association;
        }
    }

    protected function createAssociation( \stdClass $country ): AssociationBase
    {
        $association = new AssociationBase($country->countryCode);
        $association->setParent( $this->getDefaultAssociation() );
        $association->setId($country->countryCode);
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