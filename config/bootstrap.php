<?php

declare(strict_types=1);

ini_set("date.timezone", "UTC");
date_default_timezone_set('UTC');

use App\Handlers\HttpErrorHandler;
use App\Handlers\ShutdownHandler;
use App\ResponseEmitter\ResponseEmitter;
use DI\ContainerBuilder;
use Slim\App;
use Slim\Factory\ServerRequestCreatorFactory;

require __DIR__ . '/../vendor/autoload.php';

$containerBuilder = new ContainerBuilder();
// Set up settings
$containerBuilder->addDefinitions(__DIR__ . '/container.php');
$containerBuilder->addDefinitions(__DIR__ . '/repositories.php');
if (isset($_SERVER['REQUEST_METHOD']) === false) {
    $containerBuilder->addDefinitions(__DIR__ . '/commands.php');
}
// Build PHP-DI Container instance
$container = $containerBuilder->build();
// Create App instance
$app = $container->get(App::class);
// Register routes
(require __DIR__ . '/routes.php')($app);
// Register middleware
(require __DIR__ . '/middleware.php')($app);

// Init translator instance
// $container->get(Translator::class);

return $app;

//
//// Instantiate PHP-DI ContainerBuilder
//$containerBuilder = new ContainerBuilder();
//
//if (false) { // Should be set to true in production
//    $containerBuilder->enableCompilation(__DIR__ . '/../var/cache');
//}
//


///** @var bool $displayErrorDetails */
//$displayErrorDetails = $container->get('settings')['displayErrorDetails'];
///** @var string $origin */
//$origin = $container->get('settings')['www']['wwwurl'];
//

//
//// Create Error Handler
//$responseFactory = $app->getResponseFactory();
//$errorHandler = new HttpErrorHandler($callableResolver, $responseFactory);
//
//// Create Shutdown Handler
//$shutdownHandler = new ShutdownHandler($origin, $request, $errorHandler, $displayErrorDetails);
//register_shutdown_function($shutdownHandler);
//
//// Run App & Emit Response
//$response = $app->handle($request);
//$responseEmitter = new ResponseEmitter( $origin );
//$responseEmitter->emit($response);
