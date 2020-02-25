<?php
/**
 * Created by PhpStorm.
 * User: coen
 * Date: 28-1-17
 * Time: 21:36
 */

namespace App\Actions;

use JMS\Serializer\Serializer;
use FCToernooi\User\Repository as UserRepository;

final class User
{
    /**
     * @var UserRepository
     */
	private $userRepository;
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
		return $response->withStatus(404)->write('geen gebruiker met het opgegeven id gevonden');
	}
}