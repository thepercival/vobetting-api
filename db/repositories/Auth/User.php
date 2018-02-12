<?php
/**
 * Created by PhpStorm.
 * User: coen
 * Date: 28-1-17
 * Time: 21:30
 */

namespace VOBettingRepository\Auth;

// use Doctrine\ORM\EntityManager;

class User extends \VOBettingRepository\Main
{
	public function getByName( $arrWhere )
	{
		return $this->findOneBy( $arrWhere );

		/*$dql = "SELECT b, e, r FROM Bug b JOIN b.engineer e JOIN b.reporter r ORDER BY b.created DESC";
		$query = $this->getEntityManager()->createQuery($dql);
		return $query->getResult();*/
	}
}