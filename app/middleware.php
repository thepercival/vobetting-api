<?php

use FCToernooi\Token;
// use Crell\ApiProblem\ApiProblem;
use Gofabian\Negotiation\NegotiationMiddleware;
// use Micheh\Cache\CacheUtil;
use Tuupola\Middleware\JwtAuthentication;
use Tuupola\Middleware\CorsMiddleware;
use App\Response\Unauthorized;
use App\Middleware\Authentication;
use \JMS\Serializer\SerializerBuilder;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\DeserializationContext;
use JMS\Serializer\ContextFactory\CallableSerializationContextFactory;
use JMS\Serializer\ContextFactory\CallableDeserializationContextFactory;

$container = $app->getContainer();
$container["token"] = function ($container) {
    return new Token;
};

$container["JwtAuthentication"] = function ($container) {
    return new JwtAuthentication([
        "secret" => $container->get('settings')['auth']['jwtsecret'],
        "logger" => $container["logger"],
        "attribute" => false,
        "rules" => [
            new JwtAuthentication\RequestPathRule([
                "path" => "/",
                "ignore" => [
                    "/auth/register", "/auth/login","/auth/passwordreset","/auth/passwordchange",
                    "/tournamentspublic", "/voetbal/structures"
                ]
            ]),
            new JwtAuthentication\RequestMethodRule([
                "ignore" => ["OPTIONS"]
            ])
        ],
        "error" => function ($response, $arguments) {
            $message = $arguments["message"];
            if( $message === "Expired Token" ) {
                $message = "token is niet meer geldig, log opnieuw in";
            }
            return new Unauthorized($message, 401);
        },
        "before" => function ($request, $arguments) use ($container) {
            $container["token"]->populate($arguments["decoded"]);
        }
    ]);
};

$container["CorsMiddleware"] = function ($container) {
    return new CorsMiddleware([
        "logger" => $container["logger"],
        "origin" => $container->get('settings')['www']['urls'],
        "methods" => ["GET", "POST", "PUT", "PATCH", "DELETE"],
        "headers.allow" => ["Authorization", "If-Match", "If-Unmodified-Since","Content-Type","X-Api-Version"],
        "headers.expose" => ["Authorization", "Etag"],
        "credentials" => true,
        "cache" => 300,
        "error" => function ($request, $response, $arguments) {
            return new Unauthorized($arguments["message"], 401);
        }
    ]);
};
$container["NegotiationMiddleware"] = function ($container) {
    return new NegotiationMiddleware([
        "accept" => ["application/json"]
    ]);
};

$container["MyAuthentication"] = function ($container) {
    return new Authentication(
        $container->get('token'),
        new VOBetting\User\Repository($container->get('em'),$container->get('em')->getClassMetaData(VOBetting\User::class)),
        new VOBetting\Tournament\Repository($container->get('em'),$container->get('em')->getClassMetaData(VOBetting\Tournament::class)),
        $container->get('toernooi'),
        $container->get('voetbal')
    );
};

$app->add("MyAuthentication"); // needs executed after jwtauth for userid
$app->add("JwtAuthentication");
$app->add("CorsMiddleware");
$app->add("NegotiationMiddleware");

$app->add(function ( $request,  $response, callable $next) {
    $apiVersion = $request->getHeaderLine('X-Api-Version');
    if( strlen( $apiVersion ) === 0 ) {
        $apiVersion = "1";
    }

    $this['serializer'] = function() use ($apiVersion) {
        $settings = $this['settings'];
        $serializerBuilder = SerializerBuilder::create()->setDebug($settings['displayErrorDetails']);
        if( $settings["environment"] === "production") {
            $serializerBuilder = $serializerBuilder->setCacheDir($settings['serializer']['cache_dir']);
        }
        $serializerBuilder->setPropertyNamingStrategy(new \JMS\Serializer\Naming\SerializedNameAnnotationStrategy(new \JMS\Serializer\Naming\IdenticalPropertyNamingStrategy()));

        $serializerBuilder->setSerializationContextFactory(function () use ($apiVersion) {
            return SerializationContext::create()
                ->setGroups(array('Default'))
                ->setVersion($apiVersion);
        });
        $serializerBuilder->setDeserializationContextFactory(function () use ($apiVersion) {
            return DeserializationContext::create()
                ->setGroups(array('Default'))
                ->setVersion($apiVersion);
        });
        foreach( $settings['serializer']['yml_dir'] as $ymlnamespace => $ymldir ){
            $serializerBuilder->addMetadataDir($ymldir,$ymlnamespace);
        }
        return $serializerBuilder->build();
    };

    $response = $next($request, $response);
    header_remove("X-Powered-By");
    return $response;
});

$container["cache"] = function ($container) {
    return new CacheUtil;
};



