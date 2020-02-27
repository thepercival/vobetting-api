<?php
declare(strict_types=1);

use App\Middleware\AuthenticationMiddleware;
use App\Middleware\TournamentMiddleware;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use App\Response\UnauthorizedResponse;
use App\Response\ErrorResponse;
use Gofabian\Negotiation\NegotiationMiddleware;
use Selective\Config\Configuration;
use Tuupola\Middleware\JwtAuthentication;
use App\Middleware\CorsMiddleware;
use Slim\Exception\HttpMethodNotAllowedException;
use Slim\Exception\HttpNotFoundException;
use Psr\Log\LoggerInterface;
use Slim\App;
use VOBetting\Token as AuthToken;

// middleware is executed by LIFO

return function (App $app) {
    $container = $app->getContainer();
    $config = $container->get(Configuration::class);

    $app->add(
        function (Request $request, RequestHandler $handler): Response {
            $response = $handler->handle($request);
            header_remove("X-Powered-By");
            return $response; // ->withoutHeader('X-Powered-By');
        }
    );

    $app->add(AuthenticationMiddleware::class);

    $app->add( new JwtAuthentication(
                   [
                       "secret" => $config->getString('auth.password'),
                       "logger" => $container->get(LoggerInterface::class),
                       "rules" => [
                           new JwtAuthentication\RequestPathRule(
                               [
                                   "path" => "/",
                                   "ignore" => ["/public"]
                               ]
                           ),
                           new JwtAuthentication\RequestMethodRule(
                               [
                                   "ignore" => ["OPTIONS"]
                               ]
                           )
                       ],
                       "error" => function (Response $response, $arguments) {
                return new UnauthorizedResponse($arguments["message"]);
            },
            "before" => function ( Request $request, $arguments) {
                $token = new AuthToken( $arguments["decoded"] );
                return $request->withAttribute("token", $token);
            }
                   ]
               )
    );

    $app->add((new Middlewares\ContentType(['html', 'json']))->errorResponse());
    $app->add((new Middlewares\ContentType())->charsets(['UTF-8'])->errorResponse());
    $app->add((new Middlewares\ContentEncoding(['gzip', 'deflate'])));

    $app->add(new CorsMiddleware($config->getString("www.wwwurl")));

    // Add Routing Middleware
    $app->addRoutingMiddleware();

//    // always last, so it is called first!
    $errorMiddleware = $app->addErrorMiddleware($config->getString('environment') === "development", true, true);

    // Set the Not Found Handler
    $errorMiddleware->setErrorHandler(
        HttpNotFoundException::class,
        function (Request $request, Throwable $exception, bool $displayErrorDetails) {
            return new ErrorResponse($exception->getMessage(), 404);
        });

    // Set the Not Allowed Handler
    $errorMiddleware->setErrorHandler(
        HttpMethodNotAllowedException::class,
        function (Request $request, Throwable $exception, bool $displayErrorDetails) {
            return new ErrorResponse($exception->getMessage(), 405);
        });
};





