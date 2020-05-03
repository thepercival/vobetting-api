<?php

declare(strict_types=1);

use JMS\Serializer\SerializerBuilder;
use JMS\Serializer\SerializerInterface;
use Psr\Container\ContainerInterface;
use Doctrine\ORM\EntityManager;

use DI\ContainerBuilder;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\UidProcessor;
use Psr\Log\LoggerInterface;

use App\Mailer;
use VOBetting\ExternalSource\Factory as ExternalSourceFactory;
use Voetbal\Attacher\Association\Repository as AssociationAttacherRepository;
use Voetbal\CacheItemDb\Repository as CacheItemDbRepository;
use Voetbal\SerializationHandler\Round\NumberEvent as RoundNumberEventSubscriber;
use Voetbal\SerializationHandler\Round\Number as RoundNumberSerializationHandler;
use Voetbal\SerializationHandler\Structure as StructureSerializationHandler;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\DeserializationContext;
use Voetbal\SerializationHandler\Round as RoundSerializationHandler;
use Selective\Config\Configuration;
use Slim\App;
use Voetbal\ExternalSource\Repository as ExternalSourceRepository;
use Slim\Factory\AppFactory;

return [
    // Application settings
    Configuration::class => function (): Configuration {
        return new Configuration(require __DIR__ . '/settings.php');
    },
    App::class => function (ContainerInterface $container): App {
        AppFactory::setContainer($container);
        $app = AppFactory::create();
        $config = $container->get(Configuration::class);
        if ($config->getString("environment") === "production") {
            $routeCacheFile = $config->getString('router.cache_file');
            if ($routeCacheFile) {
                $app->getRouteCollector()->setCacheFile($routeCacheFile);
            }
        }
        return $app;
    },
    LoggerInterface::class => function (ContainerInterface $container): LoggerInterface {
        $config = $container->get(Configuration::class);

        $loggerSettings = $config->getArray('logger');
        $name = "application";
        $logger = new Logger($name);

        $processor = new UidProcessor();
        $logger->pushProcessor($processor);

        $path = $config->getString(
            "environment"
        ) === "development" ? 'php://stdout' : ($loggerSettings['path'] . $name . '.log');

        $handler = new StreamHandler($path, $loggerSettings['level']);
        $logger->pushHandler($handler);

        return $logger;
    },
    EntityManager::class => function (ContainerInterface $container): EntityManager {
        $config = $container->get(Configuration::class)->getArray('doctrine');
        $doctrineBaseConfig = $container->get(Configuration::class)->getArray('doctrine');
        // $settings = $container->get('settings')['doctrine'];
        $doctrineMetaConfig = $config['meta'];
        $doctrineConfig = Doctrine\ORM\Tools\Setup::createConfiguration(
            $doctrineMetaConfig['dev_mode'],
            $doctrineMetaConfig['proxy_dir'],
            $doctrineMetaConfig['cache']
        );
        $driver = new \Doctrine\ORM\Mapping\Driver\XmlDriver($doctrineMetaConfig['entity_path']);
        $doctrineConfig->setMetadataDriverImpl($driver);
        $em = Doctrine\ORM\EntityManager::create($config['connection'], $doctrineConfig);
        // $em->getConnection()->setAutoCommit(false);
        return $em;
    },
    SerializerInterface::class => function (ContainerInterface $container): SerializerInterface {
        $config = $container->get(Configuration::class);
        $env = $config->getString("environment");
        $serializerBuilder = SerializerBuilder::create()->setDebug($env === "development");
        if ($env === "production") {
            $serializerBuilder = $serializerBuilder->setCacheDir($config->getString('serializer.cache_dir'));
        }
        $serializerBuilder->setPropertyNamingStrategy(
            new \JMS\Serializer\Naming\SerializedNameAnnotationStrategy(
                new \JMS\Serializer\Naming\IdenticalPropertyNamingStrategy()
            )
        );
        $serializerBuilder->setSerializationContextFactory(
            function (): SerializationContext {
                return SerializationContext::create()->setGroups(['Default']);
            }
        );
        $serializerBuilder->setDeserializationContextFactory(
            function (): DeserializationContext {
                return DeserializationContext::create()->setGroups(['Default']);
            }
        );
        foreach ($config->getArray('serializer.yml_dir') as $ymlnamespace => $ymldir) {
            $serializerBuilder->addMetadataDir($ymldir, $ymlnamespace);
        }
        $serializerBuilder->configureHandlers(
            function (JMS\Serializer\Handler\HandlerRegistry $registry): void {
                $registry->registerSubscribingHandler(new StructureSerializationHandler());
                $registry->registerSubscribingHandler(new RoundNumberSerializationHandler());
                $registry->registerSubscribingHandler(new RoundSerializationHandler());
//            $registry->registerSubscribingHandler(new QualifyGroupSerializationHandler());
            }
        );
//            $serializerBuilder->configureListeners(function(JMS\Serializer\EventDispatcher\EventDispatcher $dispatcher) {
//                /*$dispatcher->addListener('serializer.pre_serialize',
//                    function(JMS\Serializer\EventDispatcher\PreSerializeEvent $event) {
//                        // do something
//                    }
//                );*/
//                //$dispatcher->addSubscriber(new RoundNumberEventSubscriber());
//                $dispatcher->addSubscriber(new RoundNumberEventSubscriber());
//            });
        $serializerBuilder->addDefaultHandlers();

        return $serializerBuilder->build();
    },
    Mailer::class => function (ContainerInterface $container): Mailer {
        $config = $container->get(Configuration::class);
        return new Mailer(
            $container->get(LoggerInterface::class),
            $config->getString('email.from'),
            $config->getString('email.fromname'),
            $config->getString('email.admin'),
        );
    },
    ExternalSourceFactory::class => function (ContainerInterface $container): ExternalSourceFactory {
        return new ExternalSourceFactory(
            $container->get(ExternalSourceRepository::class),
            $container->get(CacheItemDbRepository::class),
            $container->get(LoggerInterface::class)
        );
    }

];
