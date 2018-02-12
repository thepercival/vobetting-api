<?php
// Application middleware
use \Slim\Middleware\JwtAuthentication;

$settings = $app->getContainer()->get('settings');

$app->add(
// $app->getContainer()->get('jwtauth')
    new JwtAuthentication([
        "secure" => true,
        "relaxed" => ["localhost"],
        "secret" => $app->getContainer()->get('settings')['auth']['jwtsecret'],
        // "algorithm" => $app->getContainer()->get('settings')['auth']['jwtalgorithm'], default
        "rules" => [
            new JwtAuthentication\RequestPathRule([
                "path" => "/",
                "passthrough" => ["/auth/register", "/auth/login","/auth/passwordreset","/auth/passwordchange"]
            ]),
            new JwtAuthentication\RequestMethodRule([
                "path" => "/test",
                "passthrough" => ["GET"]
            ]),
            new JwtAuthentication\RequestMethodRule([
                "passthrough" => ["OPTIONS"]
            ])
        ],
        "callback" => function ($request, $response, $arguments) use ($container) {
            $container["jwt"] = $arguments["decoded"];
        }
    ])
);

$app->add( function ($request, $response, $next) use ( $app ) {

    $response = $next($request, $response);

    return $response
        ->withHeader('Access-Control-Allow-Origin', $app->getContainer()->get('settings')['www']['url'] )
        ->withHeader('Access-Control-Allow-Credentials', 'true')
        ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Origin, Content-Type, Accept, Authorization')
        ->withHeader('Access-Control-Allow-Methods', 'GET,POST,PUT,DELETE,OPTIONS');
});