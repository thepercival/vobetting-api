<?php

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

return [
    'environment' => getenv('ENVIRONMENT'),
    'displayErrorDetails' => (getenv('ENVIRONMENT') === "development"),
    // Renderer settings
    'renderer' => [
        'template_path' => __DIR__ . '/../templates/',
    ],
    // Serializer(JMS)
    'serializer' => [
        'cache_dir' => __DIR__ . '/../cache/serializer',
        'yml_dir' => [
            "Voetbal" => __DIR__ . '/../vendor/thepercival/voetbal/serialization/yml',
            "VOBetting" => __DIR__ . '/../serialization/yml'
        ],
    ],
    // Monolog settings
    'logger' => [
        'path' => __DIR__ . '/../logs/',
        'level' => (getenv('ENVIRONMENT') === "development" ? \Monolog\Logger::DEBUG : \Monolog\Logger::ERROR),
    ],
    'router' => [
        'cache_file' => __DIR__ . '/../cache/router',
    ],
    // Doctrine settings
    'doctrine' => [
        'meta' => [
            'entity_path' => [
                __DIR__ . '/../vendor/thepercival/voetbal/db/doctrine-mappings',
                __DIR__ . '/../db/doctrine-mappings'
            ],
            'dev_mode' => (getenv('ENVIRONMENT') === "development"),
            'proxy_dir' => __DIR__ . '/../cache/proxies',
            'cache' => null,
        ],
        'connection' => [
            'driver' => 'pdo_mysql',
            'host' => getenv('DB_HOST'),
            'dbname' => getenv('DB_NAME'),
            'user' => getenv('DB_USERNAME'),
            'password' => getenv('DB_PASSWORD'),
            'charset' => 'utf8mb4',
            'driverOptions' => array(
                1002 => "SET NAMES 'utf8mb4' COLLATE 'utf8mb4_general_ci'"
            )
        ],
        'serializer' => array(
            'enabled' => true
        ),
    ],
    'auth' => [
        'password' => getenv('AUTH_PASSWORD')
    ],
    'www' => [
        'wwwurl' => getenv('WWW_URL'),
        'wwwurl-localpath' => realpath(__DIR__ . "/../../") . "/vobetting/dist/",
        'apiurl' => getenv('API_URL'),
        "apiurl-localpath" => realpath(__DIR__ . '/../public/') . '/',
    ],
    'email' => [
        'from' => "info@vobetting.nl",
        'fromname' => "VOBetting",
        'admin' => "coendunnink@gmail.com"
    ],
    'images' => [
    ],
];
