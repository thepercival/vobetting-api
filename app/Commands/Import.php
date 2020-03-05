<?php

namespace App\Commands;

use Psr\Container\ContainerInterface;
use App\Command;
use Selective\Config\Configuration;

use Symfony\Component\Console\Input\InputInterface;
use Voetbal\External\System\Repository as ExternalSystemRepository;
use Voetbal\External\System\Factory as ExternalSystemFactory;

class Import extends Command
{
    /**
     * @var ExternalSystemRepository
     */
    protected $externalSystemRepos;
    /**
     * @var ExternalSystemFactory
     */
    protected $externalSystemFactory;

    public function __construct(ContainerInterface $container)
    {
        // $settings = $container->get('settings');
        $this->externalSystemRepos = $container->get(ExternalSystemRepository::class);

        parent::__construct($container->get(Configuration::class));
    }

    protected function init( InputInterface $input, string $name ) {
        $this->initLogger( $input, $name );
        $this->initExternalSystemFactory();
    }

    protected function initExternalSystemFactory() {

        $this->externalSystemFactory = new ExternalSystemFactory( $this->logger );
    }
}