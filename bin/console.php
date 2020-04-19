<?php

use Psr\Container\ContainerInterface;
use Selective\Config\Configuration;
use Symfony\Component\Console\Application;

if (isset($_SERVER['REQUEST_METHOD'])) {
    echo "Only CLI allowed. Script stopped.\n";
    exit(1);
}
/** @var ContainerInterface $container */
$container = (require __DIR__ . '/../config/bootstrap.php')->getContainer();

try {
    $command = null;
    if (array_key_exists(1, $argv) === false) {
        throw new \Exception("add a parameter with the actionname", E_ERROR);
    }
    $command = (string)$argv[1];

    $application = new Application();
    $application->add($container->get($command));
    $application->run();
} catch (\Exception $e) {
    echo $e->getMessage() . PHP_EOL;
}
