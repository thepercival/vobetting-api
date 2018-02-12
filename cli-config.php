<?php

use Doctrine\ORM\Tools\Console\ConsoleRunner;

require 'vendor/autoload.php';

$settings = include 'app/settings.php';
$settings = $settings['settings']['doctrine'];

class CustomYamlDriver extends Doctrine\ORM\Mapping\Driver\YamlDriver
{
	protected function loadMappingFile($file)
	{
		return Symfony\Component\Yaml\Yaml::parse(file_get_contents($file), Symfony\Component\Yaml\Yaml::PARSE_CONSTANT);
	}
}

$config = \Doctrine\ORM\Tools\Setup::createConfiguration(
	$settings['meta']['auto_generate_proxies'],
	$settings['meta']['proxy_dir'],
	$settings['meta']['cache']
);
$config->setMetadataDriverImpl( new CustomYamlDriver( $settings['meta']['entity_path'] ));

$em = \Doctrine\ORM\EntityManager::create($settings['connection'], $config);

return ConsoleRunner::createHelperSet($em);