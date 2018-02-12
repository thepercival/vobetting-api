<?php
/**
 * Created by PhpStorm.
 * User: coen
 * Date: 28-1-17
 * Time: 21:20
 */

namespace VOBetting\Auth;

use VOBetting\Auth\User;
use VOBettingRepository\Auth\User as UserRepository;

class Service
{
	/**
	 * @var UserRepository
	 */
	protected $repos;

	/**
	 * Service constructor.
	 *
	 * @param UserRepository $userRepository
	 */
	public function __construct( UserRepository $userRepository )
	{
		$this->repos = $userRepository;
	}

	/**
	 * @param User\Name $name
	 * @param string $password
	 * @param User\Emailaddress $emailaddress
	 *
	 * @throws \Exception
	 */
	public function register( User\Name $name, $password, User\Emailaddress $emailaddress )
	{
		//if ( "coen" != $name ){
		//	throw new \Exception("alleen de gebruikersnaam coen kan geregistreerd worden");
		//}
		$userTmp = $this->repos->findOneBy( array('name' => $name ) );
		if ( $userTmp ) {
			throw new \Exception("de gebruikersnaam is al in gebruik",E_ERROR);
		}
		$userTmp = $this->repos->findOneBy( array('emailaddress' => $emailaddress ) );
		if ( $userTmp ) {
			throw new \Exception("het emailadres is al in gebruik",E_ERROR);
		}
        $hashedPassword = password_hash( $password, PASSWORD_DEFAULT);
        // throw new \Exception($password." x ".$hashedPassword,E_ERROR);
		$user = new User($name, $hashedPassword, $emailaddress);

		return $this->repos->save($user);
	}


}