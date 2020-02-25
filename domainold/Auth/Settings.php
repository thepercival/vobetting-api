<?php
/**
 * Created by PhpStorm.
 * User: coen
 * Date: 28-1-17
 * Time: 21:20
 */

namespace FCToernooi\Auth;

use Doctrine\DBAL\Connection;
use FCToernooi\User;
use FCToernooi\User\Repository as UserRepository;
use FCToernooi\Role\Repository as RoleRepository;
use FCToernooi\Role;
use FCToernooi\Tournament\Repository as TournamentRepository;
use Firebase\JWT\JWT;
use Tuupola\Base62;

class Settings
{
	/**
	 * @var string
	 */
	protected $jwtSecret;
    /**
     * @var string
     */
    protected $jwtAlgorithm;
    /**
     * @var string
     */
    protected $activationSecret;

	public function __construct(
	    string $jwtSecret,
        string $jwtAlgorithm,
        string $activationSecret )
	{
		$this->jwtSecret = $jwtSecret;
        $this->jwtAlgorithm = $jwtAlgorithm;
        $this->activationSecret = $activationSecret;
	}

	public function getJwtSecret(): string {
	    return $this->jwtSecret;
    }

    public function getJwtAlgorithm(): string {
        return $this->jwtAlgorithm;
    }

    public function getActivationSecret(): string {
        return $this->activationSecret;
    }
}