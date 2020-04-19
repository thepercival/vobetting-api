<?php

use Doctrine\ORM\Tools\Console\ConsoleRunner;

require 'vendor/autoload.php';

$settings = include 'config/settings.php';
$settings = $settings['doctrine'];

$config = \Doctrine\ORM\Tools\Setup::createConfiguration(
    $settings['meta']['dev_mode'],
    $settings['meta']['proxy_dir'],
    $settings['meta']['cache']
);
$driver = new \Doctrine\ORM\Mapping\Driver\XmlDriver($settings['meta']['entity_path']);
$config->setMetadataDriverImpl($driver);

$em = \Doctrine\ORM\EntityManager::create($settings['connection'], $config);

return ConsoleRunner::createHelperSet($em);
