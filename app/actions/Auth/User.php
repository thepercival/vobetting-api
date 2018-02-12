<?php
/**
 * Created by PhpStorm.
 * User: coen
 * Date: 28-1-17
 * Time: 21:36
 */

namespace App\Action\Auth;

use Slim\ServerRequestInterface;
use JMS\Serializer\Serializer;
use VOBettingRepository\Auth\User as UserRepository;
use VOBetting\Auth;

final class User
{
    /**
     * @var UserRepository
     */
	private $userRepository;
    /**
     * @var Auth\Service
     */
    private $authService;
    /**
     * @var Serializer
     */
	protected $serializer;
    /**
     * @var array
     */
	protected $settings;

	public function __construct(UserRepository $userRepository, Serializer $serializer, $settings )
	{
		$this->userRepository = $userRepository;
		$this->authService = new Auth\Service($userRepository);
		$this->serializer = $serializer;
		$this->settings = $settings;
	}

	public function fetch($request, $response, $args)
	{
		$users = $this->userRepository->findAll();
		return $response
			->withHeader('Content-Type', 'application/json;charset=utf-8')
			->write($this->serializer->serialize( $users, 'json'));
		;
	}

	public function fetchOne($request, $response, $args)
	{
		$user = $this->userRepository->find($args['id']);
		if ($user) {
			return $response
				->withHeader('Content-Type', 'application/json;charset=utf-8')
				->write($this->serializer->serialize( $user, 'json'));
			;
		}
		return $response->withStatus(404, 'geen gebruiker met het opgegeven id gevonden');
	}
}