<?php
/**
 * Created by PhpStorm.
 * User: coen
 * Date: 30-1-17
 * Time: 12:48
 */

namespace App\Actions;

use App\Exceptions\DomainRecordNotFoundException;
use App\Response\ErrorResponse;
use JMS\Serializer\SerializerInterface;
use FCToernooi\User;
use \Firebase\JWT\JWT;
use FCToernooi\User\Repository as UserRepository;
use FCToernooi\Auth\Service as AuthService;
use \Slim\Middleware\JwtAuthentication;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Tuupola\Base62;

final class Auth extends Action
{
    /**
     * @var AuthService
     */
	private $authService;
    /**
     * @var UserRepository
     */
    private $userRepository;
    /**
     * @var SerializerInterface
     */
    protected $serializer;

	public function __construct(AuthService $authService, UserRepository $userRepository, SerializerInterface $serializer )
	{
        $this->authService = $authService;
        $this->userRepository = $userRepository;
		$this->serializer = $serializer;
	}

    public function validateToken( Request $request, Response $response, $args ): Response
    {
        return $response->withStatus(200);
    }

    public function login( Request $request, Response $response, $args): Response
	{
       try{
           $authData = $this->getFormData( $request );
           if( !property_exists( $authData, "emailaddress") || strlen($authData->emailaddress) === 0 ) {
               throw new \Exception( "het emailadres is niet opgegeven");
           }
           $emailaddress = filter_var($authData->emailaddress, FILTER_VALIDATE_EMAIL);
           if( $emailaddress === false ) {
               throw new \Exception( "het emailadres \"".$authData->emailaddress."\" is onjuist");
           }
           if( !property_exists( $authData, "password") || strlen($authData->password) === 0 ) {
               throw new \Exception( "het wachtwoord is niet opgegeven");
           }

           $user = $this->userRepository->findOneBy(
               array( 'emailaddress' => $emailaddress )
           );

           if (!$user or !password_verify( $user->getSalt() . $authData->password, $user->getPassword() ) ) {
               throw new \Exception( "ongeldige emailadres en wachtwoord combinatie");
           }

           /*if ( !$user->getActive() ) {
		    throw new \Exception( "activeer eerst je account met behulp van de link in je ontvangen email", E_ERROR );
		    }*/

           $data = [
               "token" => $this->authService->getToken( $user ),
               "userid" => $user->getId()
           ];

           return $this->respondWithJson( $response, $this->serializer->serialize( $data, 'json') );
		}
		catch( \Exception $e ){
            return new ErrorResponse($e->getMessage(), 422);
		}
	}
}