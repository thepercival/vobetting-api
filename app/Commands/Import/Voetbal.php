<?php

namespace App\Commands\Import;

use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Voetbal\Import\Service as VoetbalImportService;
use App\Commands\Import as ImportCommand;
use Voetbal\Association\Repository as AssociationRepository;
use Voetbal\Attacher\Association\Repository as AssociationAttacherRepository;
use Voetbal\CacheItemDb\Repository as CacheItemDbRepository;
use Voetbal\Season\Repository as SeasonRepository;
use Voetbal\Attacher\Season\Repository as SeasonAttacherRepository;
use Voetbal\League\Repository as LeagueRepository;
use Voetbal\Attacher\League\Repository as LeagueAttacherRepository;

class Voetbal extends ImportCommand
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
        $this->addOption('seasons', null, InputOption::VALUE_NONE, 'seasons');
        $this->addOption('leagues', null, InputOption::VALUE_NONE, 'leagues');

        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->init($input, 'cron-import-voetbal');

        $externalSources = $this->externalSourceRepos->findAll();
        $cacheItemRepos = $this->container->get( CacheItemDbRepository::class );
        $importService = new VoetbalImportService( $externalSources, $cacheItemRepos, $this->logger );
        if(  $input->getOption("associations") ) {
            $associationRepos = $this->container->get( AssociationRepository::class );
            $associationAttacherRepos = $this->container->get( AssociationAttacherRepository::class );
            $importService->importAssociations( $associationRepos , $associationAttacherRepos);
        }

        if(  $input->getOption("seasons") ) {
            $seasonRepos = $this->container->get( SeasonRepository::class );
            $seasonAttacherRepos = $this->container->get( SeasonAttacherRepository::class );
            $importService->importSeasons( $seasonRepos , $seasonAttacherRepos);
        }

        if(  $input->getOption("leagues") ) {
            $leagueRepos = $this->container->get( LeagueRepository::class );
            $leagueAttacherRepos = $this->container->get( LeagueAttacherRepository::class );
            $associationAttacherRepos = $this->container->get( AssociationAttacherRepository::class );
            $importService->importLeagues( $leagueRepos, $leagueAttacherRepos, $associationAttacherRepos);
        }
        
        return 0;
    }
}