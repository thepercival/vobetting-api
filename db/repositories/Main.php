<?php
/**
 * Created by PhpStorm.
 * User: coen
 * Date: 29-1-17
 * Time: 9:52
 */

namespace VOBettingRepository;

use Doctrine\ORM\EntityRepository;

class Main extends EntityRepository
{
	/**
	 * Main constructor.
	 *
	 * @param \Doctrine\ORM\EntityManager $em
	 * @param \Doctrine\ORM\Mapping\ClassMetadata $vtResource
	 *
	 * @throws \Exception
	 */
	public function __construct($em, $vtResource)
	{
		if ( is_string( $vtResource ) ) {
			$sClass = null;
			if( $vtResource === "associations")
				$sClass = \Voetbal\Association::class;

			if ( $sClass === null )
				throw new \Exception("resource ".$vtResource." not found", E_ERROR );

			$vtResource = $em->getClassMetaData($sClass);
		}

		parent::__construct( $em, $vtResource);
	}

	public function save( $object )
	{
		$this->_em->persist($object);
		$this->_em->flush();
		return $object;
	}
}