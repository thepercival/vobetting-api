<?php

namespace App\Commands;

use Psr\Container\ContainerInterface;
use App\Command;
use Selective\Config\Configuration;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Voetbal\Attacher\Sport\Repository as SportAttacherRepository;
use Voetbal\Attacher\Association\Repository as AssociationAttacherRepository;
use Voetbal\Attacher\League\Repository as LeagueAttacherRepository;
use Voetbal\Attacher\Season\Repository as SeasonAttacherRepository;
use Voetbal\Attacher\Competition\Repository as CompetitionAttacherRepository;
use Voetbal\Attacher\Competitor\Repository as CompetitorAttacherRepository;

use VOBetting\ExternalSource\Factory as ExternalSourceFactory;
use Voetbal\ExternalSource\SofaScore;
use VOBetting\ExternalSource\Betfair;
use Voetbal\Import\Service as VoetbalImportService;
use Voetbal\Sport\Repository as SportRepository;
use Voetbal\Association\Repository as AssociationRepository;
use Voetbal\League\Repository as LeagueRepository;
use Voetbal\Season\Repository as SeasonRepository;
use Voetbal\Competition\Repository as CompetitionRepository;
use Voetbal\Competitor\Repository as CompetitorRepository;
use Voetbal\Structure\Repository as StructureRepository;

class Import extends Command
{
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
        $this->externalSourceFactory = $container->get(ExternalSourceFactory::class);
        $this->container = $container;
        parent::__construct($container->get(Configuration::class));
    }

    protected function configure()
    {
        $this
            // the name of the command (the part after "bin/console")
            ->setName('app:import')
            // the short description shown while running "php bin/console list"
            ->setDescription('imports the objects')
            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('import the objects');

        $this->addOption('sports', null, InputOption::VALUE_NONE, 'sports');
        $this->addOption('associations', null, InputOption::VALUE_NONE, 'associations');
        $this->addOption('seasons', null, InputOption::VALUE_NONE, 'seasons');
        $this->addOption('leagues', null, InputOption::VALUE_NONE, 'leagues');
        $this->addOption('competitions', null, InputOption::VALUE_NONE, 'competitions');
        $this->addOption('competitors', null, InputOption::VALUE_NONE, 'competitors');
        $this->addOption('structures', null, InputOption::VALUE_NONE, 'structure');

        parent::configure();
    }



    protected function init( InputInterface $input, string $name ) {
        $this->initLogger( $input, $name );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->init($input, 'cron-import');

        $importService = new VoetbalImportService($this->logger);

        if ($input->getOption("sports")) {
            $sofaScore = $this->externalSourceFactory->createByName( SofaScore::NAME );
            $sportRepos = $this->container->get(SportRepository::class);
            $sportAttacherRepos = $this->container->get(SportAttacherRepository::class);
            $importService->importSports($sofaScore, $sportRepos, $sportAttacherRepos);
        }
        if ($input->getOption("associations")) {
            $betFair = $this->externalSourceFactory->createByName( Betfair::NAME );
            $associationRepos = $this->container->get(AssociationRepository::class);
            $associationAttacherRepos = $this->container->get(AssociationAttacherRepository::class);
            $importService->importAssociations($betFair, $associationRepos, $associationAttacherRepos);
        }
        // so season input manual
        if ($input->getOption("seasons")) {
//            $sofaScore = $this->externalSourceFactory->createByName( SofaScore::NAME );
//            $seasonRepos = $this->container->get(SeasonRepository::class);
//            $seasonAttacherRepos = $this->container->get(SeasonAttacherRepository::class);
//            $importService->importSeasons($sofaScore, $seasonRepos, $seasonAttacherRepos);
        }
        if ($input->getOption("leagues")) {
            $sofaScore = $this->externalSourceFactory->createByName( SofaScore::NAME );
            $leagueRepos = $this->container->get(LeagueRepository::class);
            $leagueAttacherRepos = $this->container->get(LeagueAttacherRepository::class);
            $associationAttacherRepos = $this->container->get(AssociationAttacherRepository::class);
            $importService->importLeagues($sofaScore, $leagueRepos, $leagueAttacherRepos, $associationAttacherRepos);
        }
        if ($input->getOption("competitions")) {
            $sofaScore = $this->externalSourceFactory->createByName( SofaScore::NAME );
            $competitionRepos = $this->container->get(CompetitionRepository::class);
            $competitionAttacherRepos = $this->container->get(CompetitionAttacherRepository::class);
            $leagueAttacherRepos = $this->container->get(LeagueAttacherRepository::class);
            $seasonAttacherRepos = $this->container->get(SeasonAttacherRepository::class);
            $sportAttacherRepos = $this->container->get(SportAttacherRepository::class);
            $importService->importCompetitions(
                $sofaScore,
                $competitionRepos,
                $competitionAttacherRepos,
                $leagueAttacherRepos,
                $seasonAttacherRepos,
                $sportAttacherRepos
            );
        }
        if ($input->getOption("competitors")) {
            $sofaScore = $this->externalSourceFactory->createByName( SofaScore::NAME );
            $competitorRepos = $this->container->get(CompetitorRepository::class);
            $competitorAttacherRepos = $this->container->get(CompetitorAttacherRepository::class);
            $associationAttacherRepos = $this->container->get(AssociationAttacherRepository::class);
            $competitionAttacherRepos = $this->container->get(CompetitionAttacherRepository::class);
            $importService->importCompetitors(
                $sofaScore,
                $competitorRepos,
                $competitorAttacherRepos,
                $associationAttacherRepos,
                $competitionAttacherRepos
            );
        }

        if ($input->getOption("structures")) {
            $sofaScore = $this->externalSourceFactory->createByName( SofaScore::NAME );
            $structureRepos = $this->container->get(StructureRepository::class);
            $competitorAttacherRepos = $this->container->get(CompetitorAttacherRepository::class);
            $competitionAttacherRepos = $this->container->get(CompetitionAttacherRepository::class);
            $importService->importStructures(
                $sofaScore,
                $structureRepos,
                $competitorAttacherRepos,
                $competitionAttacherRepos
            );
        }

        // testen competitors

        // structuur moet geimporteerd worden vanuit sofascore, omdat bij bv. het wk dezelfde wedstrijd binnen het toernooi kan plaats vinden


        // wedstrijden
        // events->weekMatches, events->roundMatches hebben wedstrijden, deze wedstrijden dan per wedstrijd opnemen
        // binnen de structuur

        return 0;
    }
}