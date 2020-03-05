<?php

namespace App\Commands\Import;

use Monolog\Logger;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Voetbal\External\System as ExternalSystem;
use Voetbal\Import\Service as VoetbalImportService;
use App\Commands\Import as PlanningCommand;

class Voetbal extends PlanningCommand
{
    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
    }

    protected function configure()
    {
        $this
            // the name of the command (the part after "bin/console")
            ->setName('app:import-voetbal')
            // the short description shown while running "php bin/console list"
            ->setDescription('imports the associations')
            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('import the associations')
        ;

        $this->addOption('associations', null, InputOption::VALUE_NONE, 'associations');
        // $this->addOption('associations', null, InputOption::VALUE_NONE, 'associations');

        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->init($input, 'cron-import-voetbal');

        $externalSystems = $this->externalSystemRepos->findAll();
        $importService = new VoetbalImportService( $externalSystems, $this->logger );
        if(  $input->hasOption("associations") ) {
            $importService->importAssociations();
        }
        return 0;
    }
}