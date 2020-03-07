<?php

namespace App\Commands;

use Psr\Container\ContainerInterface;
use App\Command;
use Selective\Config\Configuration;

use Symfony\Component\Console\Input\InputInterface;
use Voetbal\ExternalSource\Repository as ExternalSourceRepository;
use Voetbal\ExternalSource\Factory as ExternalSourceFactory;

class Import extends Command
{
    /**
     * @var ExternalSourceRepository
     */
    protected $externalSourceRepos;
    /**
     * @var ExternalSourceFactory
     */
    protected $externalSourceFactory;
    /**
     * @var ContainerInterface
     */
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        // $settings = $container->get('settings');
        $this->externalSourceRepos = $container->get(ExternalSourceRepository::class);

        $this->container = $container;
        parent::__construct($container->get(Configuration::class));
    }

    protected function init( InputInterface $input, string $name ) {
        $this->initLogger( $input, $name );
        $this->initExternalSourceFactory();
    }

    protected function initExternalSourceFactory() {

        $this->externalSourceFactory = new ExternalSourceFactory( $this->logger );
    }
}