<?php
declare(strict_types=1);

namespace App\Actions;

use App\Domain\DomainException\DomainRecordNotFoundException;
use mysql_xdevapi\Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Log\LoggerInterface;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpNotFoundException;
use Slim\Exception\HttpMethodNotAllowedException;
use JMS\Serializer\SerializerInterface;

abstract class Action
{
    /**
     * @var LoggerInterface
     */
    protected $logger;
    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * Action constructor.
     * @param LoggerInterface $logger
     * @param SerializerInterface $serializer
     */
    public function __construct(LoggerInterface $logger, SerializerInterface $serializer)
    {
        $this->logger = $logger;
        $this->serializer = $serializer;
    }

    /**
     * @param Request  $request
     * @param Response $response
     * @param array    $args
     * @return Response
     * @throws HttpNotFoundException
     * @throws HttpBadRequestException
     */
    // abstract public function __invoke(Request $request, Response $response, $args): Response;

//
//    /**
//     * @return Response
//     * @throws DomainRecordNotFoundException
//     * @throws HttpBadRequestException
//     */
//    abstract protected function fetchOne( Request $request, Response $response, $args ): Response;
//    abstract protected function fetch( Request $request, Response $response, $args ): Response;
//    abstract protected function add( Request $request, Response $response, $args ): Response;
//    abstract protected function edit( Request $request, Response $response, $args ): Response;
//    abstract protected function remove( Request $request, Response $response, $args ): Response;
//

    public function options(Request $request, Response $response, $args): Response
    {
        return $response;
    }

    /**
     * @return array|object
     * @throws HttpBadRequestException
     */
    protected function getFormData(Request $request)
    {
        $input = json_decode($this->getRawData());
        if ($input === null) {
            return new \stdClass();
        }

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new HttpBadRequestException($request, 'Malformed JSON input.');
        }

        return $input;
    }

    protected function getRawData()
    {
        return file_get_contents('php://input');
    }

    /**
     * @param  string $name
     * @return mixed
     * @throws HttpBadRequestException
     */
    protected function resolveArg(Request $request, $args, string $name)
    {
        if (!isset($args[$name])) {
            throw new HttpBadRequestException($request, "Could not resolve argument `{$name}`.");
        }

        return $args[$name];
    }

    /**
     * @param string $json
     * @return Response
     */
    protected function respondWithJson(Response $response, string $json): Response
    {
        $response->getBody()->write($json);
        return $response->withHeader('Content-Type', 'application/json');
    }
}
