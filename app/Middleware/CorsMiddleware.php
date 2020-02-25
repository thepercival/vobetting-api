<?php


namespace App\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Http\Server\MiddlewareInterface;
use Slim\Routing\RouteContext;

/**
 * CORS middleware.
 *
 * Allows CORS preflight from any domain.
 */
final class CorsMiddleware implements MiddlewareInterface
{
    /**
     * @var string
     */
    private $origin;

    public function __construct(string $origin)
    {
        $this->origin = $origin;
    }

    public function process(Request $request, RequestHandler $handler): Response
    {
        $routeContext = RouteContext::fromRequest($request);
        $routingResults = $routeContext->getRoutingResults();
        $methods = $routingResults->getAllowedMethods();
        $requestHeaders = $request->getHeaderLine('Access-Control-Request-Headers');
        $response = $handler->handle($request);
//
//        $response = $response->withHeader('Access-Control-Allow-Headers', $requestHeaders);

        return $response
            ->withHeader('Access-Control-Allow-Credentials', 'true')
            ->withHeader('Access-Control-Allow-Origin', substr($this->origin, 0, strlen($this->origin) - 1))
            ->withHeader(
                'Access-Control-Allow-Headers',
                'X-Requested-With, Content-Type, Accept, Origin, Authorization, X-Api-Version'
            )
            ->withHeader('Access-Control-Allow-Methods', implode(',', $methods))
            ->withHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->withAddedHeader('Cache-Control', 'post-check=0, pre-check=0')
            ->withHeader('Pragma', 'no-cache');
    }
}
