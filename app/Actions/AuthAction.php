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
use Psr\Log\LoggerInterface;
use DateTimeImmutable;
use JMS\Serializer\SerializerInterface;
use \Firebase\JWT\JWT;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Selective\Config\Configuration;
use stdClass;
use Tuupola\Base62;

final class AuthAction extends Action
{
    /**
     * @var Configuration
     */
    protected $config;

    public function __construct(LoggerInterface $logger, SerializerInterface $serializer,Configuration $config) {
        parent::__construct($logger, $serializer);
        $this->config = $config;
    }

    public function validateToken(Request $request, Response $response, $args): Response
    {
        return $response->withStatus(200);
    }

    public function login(Request $request, Response $response, $args): Response
    {
        try {
            /** @var stdClass $authData */
            $authData = $this->getFormData($request);
            if (!property_exists($authData, "password") || strlen($authData->password) === 0) {
                throw new \Exception("het wachtwoord is niet opgegeven");
            }
            if (!password_verify($authData->password, $this->config->getString("auth.password"))) {
                throw new \Exception("ongeldig wachtwoord");
            }

            /*if ( !$user->getActive() ) {
             throw new \Exception( "activeer eerst je account met behulp van de link in je ontvangen email", E_ERROR );
             }*/

            $data = ["token" => $this->getToken() ];

            return $this->respondWithJson($response, $this->serializer->serialize($data, 'json'));
        } catch (\Exception $e) {
            return new ErrorResponse($e->getMessage(), 422);
        }
    }

    public function getToken()
    {
        $jti = (new Base62)->encode(random_bytes(16));

        $now = new DateTimeImmutable();
        $future = new DateTimeImmutable("now +3 months");

        $payload = [
            "iat" => $now->getTimestamp(),
            "exp" => $future->getTimestamp(),
            "jti" => $jti
        ];
        return JWT::encode($payload, $this->config->getString("auth.password"));
    }
}
