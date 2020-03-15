<?php

namespace App\Commands\Attach;

use PeterColes\Betfair\Betfair;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Voetbal\ExternalSource;
use Voetbal\Import\Service as VoetbalImportService;
use App\Commands\ExternalSource as ExternalSourceCommand;
use Voetbal\CacheItemDb\Repository as CacheItemDbRepository;
use Voetbal\Sport\Repository as SportRepository;
use Voetbal\Attacher\Sport\Repository as SportAttacherRepository;
use Voetbal\Association\Repository as AssociationRepository;
use Voetbal\Attacher\Association\Repository as AssociationAttacherRepository;
use Voetbal\Season\Repository as SeasonRepository;
use Voetbal\Attacher\Season\Repository as SeasonAttacherRepository;
use Voetbal\League\Repository as LeagueRepository;
use Voetbal\Attacher\League\Repository as LeagueAttacherRepository;
use Voetbal\Competition\Repository as CompetitionRepository;
use Voetbal\Attacher\Competition\Repository as CompetitionAttacherRepository;

class Voetbal extends ExternalSourceCommand
{
    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
    }

    protected function configure()
    {
        $this
            // the name of the command (the part after "bin/console")
            ->setName('app:attach-voetbal')
            // the short description shown while running "php bin/console list"
            ->setDescription('attaches the sport objects')
            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('import the associations');

        // $this->addOption('sports', null, InputOption::VALUE_NONE, 'sports');
        $this->addOption('associations', null, InputOption::VALUE_NONE, 'associations');
//        $this->addOption('seasons', null, InputOption::VALUE_NONE, 'seasons');
//        $this->addOption('leagues', null, InputOption::VALUE_NONE, 'leagues');
//        $this->addOption('competitions', null, InputOption::VALUE_NONE, 'competitions');

        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->init($input, 'cron-attach-voetbal');

        $externalSources = $this->externalSourceRepos->findAll();
        $cacheItemRepos = $this->container->get(CacheItemDbRepository::class);
        $importService = new VoetbalImportService($externalSources, $cacheItemRepos, $this->logger);
        
//        if ($input->getOption("sports")) {
//            $sportRepos = $this->container->get(SportRepository::class);
//            $sportAttacherRepos = $this->container->get(SportAttacherRepository::class);
//            $importService->importSports($sportRepos, $sportAttacherRepos);
//        }
        
        if ($input->getOption("associations")) {

            /** @var ExternalSource $externalSource */
            foreach( $externalSources as $externalSource ) {
                if ($externalSource->getName() !== "betfair") {
                    continue;
                }
                $betFair = new Betfair();
                $betFair->login( $externalSource->getApikey(), $externalSource->getUsername(), $externalSource->getPassword() );
                $countries = $betFair->betting(['listCountries']);

                $competitions = $betFair->betting(['listCompetitions']);

                $text = "122";

            }
        }

//        if ($input->getOption("seasons")) {
//            $seasonRepos = $this->container->get(SeasonRepository::class);
//            $seasonAttacherRepos = $this->container->get(SeasonAttacherRepository::class);
//            $importService->importSeasons($seasonRepos, $seasonAttacherRepos);
//        }
//
//        if ($input->getOption("leagues")) {
//            $leagueRepos = $this->container->get(LeagueRepository::class);
//            $leagueAttacherRepos = $this->container->get(LeagueAttacherRepository::class);
//            $associationAttacherRepos = $this->container->get(AssociationAttacherRepository::class);
//            $importService->importLeagues($leagueRepos, $leagueAttacherRepos, $associationAttacherRepos);
//        }
//
//        if ($input->getOption("competitions")) {
//            $competitionRepos = $this->container->get(CompetitionRepository::class);
//            $competitionAttacherRepos = $this->container->get(CompetitionAttacherRepository::class);
//            $leagueAttacherRepos = $this->container->get(LeagueAttacherRepository::class);
//            $seasonAttacherRepos = $this->container->get(SeasonAttacherRepository::class);
//            $sportAttacherRepos = $this->container->get(SportAttacherRepository::class);
//            $importService->importCompetitions(
//                $competitionRepos,
//                $competitionAttacherRepos,
//                $leagueAttacherRepos,
//                $seasonAttacherRepos,
//                $sportAttacherRepos
//            );
//        }

        return 0;
    }
}