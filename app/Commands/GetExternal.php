<?php

namespace App\Commands;

use LucidFrame\Console\ConsoleTable;
use Psr\Container\ContainerInterface;
use App\Command;
use Selective\Config\Configuration;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;

use VOBetting\Attacher\Bookmaker\Repository as BookmakerAttacherRepository;
use VOBetting\BetLine\Repository as BetLineRepository;
use VOBetting\LayBack\Repository as LayBackRepository;
use Voetbal\Attacher\Game\Repository as GameAttacherRepository;
use Voetbal\Attacher\Place\Repository as PlaceAttacherRepository;
use Voetbal\Attacher\Poule\Repository as PouleAttacherRepository;
use Voetbal\Attacher\Sport\Repository as SportAttacherRepository;
use Voetbal\Attacher\Association\Repository as AssociationAttacherRepository;
use Voetbal\Attacher\League\Repository as LeagueAttacherRepository;
use Voetbal\Attacher\Season\Repository as SeasonAttacherRepository;
use Voetbal\Attacher\Competition\Repository as CompetitionAttacherRepository;
use Voetbal\Attacher\Competitor\Repository as CompetitorAttacherRepository;

use VOBetting\ExternalSource\Factory as ExternalSourceFactory;
use Voetbal\ExternalSource;
use Voetbal\ExternalSource\SofaScore;
use VOBetting\ExternalSource\Betfair;
use Voetbal\Game\Repository as GameRepository;
use Voetbal\Game\Score\Repository as GameScoreRepository;
use VOBetting\Import\Service as ImportService;
use Voetbal\Sport\Repository as SportRepository;
use Voetbal\Association\Repository as AssociationRepository;
use Voetbal\League\Repository as LeagueRepository;
use Voetbal\Season\Repository as SeasonRepository;
use Voetbal\Competition\Repository as CompetitionRepository;
use Voetbal\Competitor\Repository as CompetitorRepository;
use Voetbal\Structure\Repository as StructureRepository;

class GetExternal extends Command
{
    /**
     * @var ExternalSourceFactory
     */
    protected $externalSourceFactory;
    /**
     * @var ContainerInterface
     */
    protected $container;
    /**
     * @var ImportService
     */
    protected $importService;

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
            ->setName('app:getexternal')
            // the short description shown while running "php bin/console list"
            ->setDescription('gets the external objects')
            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('import the objects');

        $this->addArgument('externalSource', InputArgument::REQUIRED, 'externalSource');
        $this->addArgument('objectType', InputArgument::REQUIRED, 'objectType');

        parent::configure();
    }

    protected function init(InputInterface $input, string $name)
    {
        $this->initLogger($input, $name);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->init($input, 'cron-getexternal');

        $this->importService = new ImportService($this->logger);

        $externalSourceName = $input->getArgument('externalSource');
        $externalSourcImpl = $this->externalSourceFactory->createByName($externalSourceName);
        if( $externalSourcImpl === null ) {
            echo "voor \"" . $externalSourceName . "\" kan er geen externe bron worden gevonden" . PHP_EOL;
            return -1;
        }

        $objectType = $input->getArgument('objectType');

        if ( $objectType === "sports" ) {
            $this->getSports($externalSourcImpl);
        } elseif ( $objectType === "associations" ) {
            $this->getAssociations($externalSourcImpl);
        }

//        if ($input->getOption("associations")) {
//            $this->importAssociations(Betfair::NAME);
//        }
////        if ($input->getOption("seasons")) { // input manual
////            $this->importSeasons();
////        }
//        if ($input->getOption("leagues")) {
//            $this->importLeagues(SofaScore::NAME);
//        }
//        if ($input->getOption("competitions")) {
//            $this->importCompetitions(SofaScore::NAME);
//        }
//        if ($input->getOption("competitors")) {
//            $this->importCompetitors(SofaScore::NAME);
//        }
//        if ($input->getOption("structures")) {
//            $this->importStructures(SofaScore::NAME);
//        }
//        if ($input->getOption("games")) {
//            $this->importGames(SofaScore::NAME);
//        }
//        if ($input->getOption("laybacks")) {
//            $this->importLayBacks([Betfair::NAME]);
//        }
        return 0;
    }

    protected function getSports(ExternalSource\Implementation $externalSourcImpl)
    {
        if( !($externalSourcImpl instanceof ExternalSource\Sport ) ) {
            echo "de externe bron \"" . $externalSourcImpl->getExternalSource()->getName() . "\" kan geen sporten opvragen" . PHP_EOL;
            return;
        }
        $table = new ConsoleTable();
        $table->setHeaders(array('Id', 'Name'));
        foreach( $externalSourcImpl->getSports() as $association ) {
            $row = array( $association->getId(), $association->getName() );
            $table->addRow( $row );
        }
        $table->display();
    }

    protected function getAssociations(ExternalSource\Implementation $externalSourcImpl)
    {
        if( !($externalSourcImpl instanceof ExternalSource\Association ) ) {
            echo "de externe bron \"" . $externalSourcImpl->getExternalSource()->getName() . "\" kan geen bonden opvragen" . PHP_EOL;
            return;
        }
        $table = new ConsoleTable();
        $table->setHeaders(array('Id', 'Name','Parent'));
        foreach( $externalSourcImpl->getAssociations() as $association ) {
            $row = array( $association->getId(), $association->getName() );
            $parentName = null;
            if( $association->getParent() !== null ) {
                $parentName = $association->getParent()->getName();
            }
            $row[] = $parentName;
            $table->addRow( $row );
        }
        $table->display();
    }

//    protected function importAssociations(string $externalSourceName)
//    {
//        $externalSourcImpl = $this->externalSourceFactory->createByName($externalSourceName);
//        $associationRepos = $this->container->get(AssociationRepository::class);
//        $associationAttacherRepos = $this->container->get(AssociationAttacherRepository::class);
//        $this->importService->importAssociations($externalSourcImpl, $associationRepos, $associationAttacherRepos);
//    }
//
//    protected function importSeasons(string $externalSourceName)
//    {
//        $externalSourcImpl = $this->externalSourceFactory->createByName($externalSourceName);
//        $seasonRepos = $this->container->get(SeasonRepository::class);
//        $seasonAttacherRepos = $this->container->get(SeasonAttacherRepository::class);
//        $this->importService->importSeasons($externalSourcImpl, $seasonRepos, $seasonAttacherRepos);
//    }
//
//    protected function importLeagues(string $externalSourceName)
//    {
//        $externalSourcImpl = $this->externalSourceFactory->createByName($externalSourceName);
//        $leagueRepos = $this->container->get(LeagueRepository::class);
//        $leagueAttacherRepos = $this->container->get(LeagueAttacherRepository::class);
//        $associationAttacherRepos = $this->container->get(AssociationAttacherRepository::class);
//        $this->importService->importLeagues($externalSourcImpl, $leagueRepos, $leagueAttacherRepos, $associationAttacherRepos);
//    }
//
//    protected function importCompetitions(string $externalSourceName)
//    {
//        $externalSourcImpl = $this->externalSourceFactory->createByName($externalSourceName);
//        $competitionRepos = $this->container->get(CompetitionRepository::class);
//        $competitionAttacherRepos = $this->container->get(CompetitionAttacherRepository::class);
//        $leagueAttacherRepos = $this->container->get(LeagueAttacherRepository::class);
//        $seasonAttacherRepos = $this->container->get(SeasonAttacherRepository::class);
//        $sportAttacherRepos = $this->container->get(SportAttacherRepository::class);
//        $this->importService->importCompetitions(
//            $externalSourcImpl,
//            $competitionRepos,
//            $competitionAttacherRepos,
//            $leagueAttacherRepos,
//            $seasonAttacherRepos,
//            $sportAttacherRepos
//        );
//    }
//
//    protected function importCompetitors(string $externalSourceName)
//    {
//        $externalSourcImpl = $this->externalSourceFactory->createByName($externalSourceName);
//        $competitorRepos = $this->container->get(CompetitorRepository::class);
//        $competitorAttacherRepos = $this->container->get(CompetitorAttacherRepository::class);
//        $associationAttacherRepos = $this->container->get(AssociationAttacherRepository::class);
//        $competitionAttacherRepos = $this->container->get(CompetitionAttacherRepository::class);
//        $this->importService->importCompetitors(
//            $externalSourcImpl,
//            $competitorRepos,
//            $competitorAttacherRepos,
//            $associationAttacherRepos,
//            $competitionAttacherRepos
//        );
//    }
//
//    protected function importStructures(string $externalSourceName)
//    {
//        $externalSourcImpl = $this->externalSourceFactory->createByName($externalSourceName);
//        $structureRepos = $this->container->get(StructureRepository::class);
//        $competitorAttacherRepos = $this->container->get(CompetitorAttacherRepository::class);
//        $competitionAttacherRepos = $this->container->get(CompetitionAttacherRepository::class);
//        $this->importService->importStructures(
//            $externalSourcImpl,
//            $structureRepos,
//            $competitorAttacherRepos,
//            $competitionAttacherRepos
//        );
//    }
//
//    protected function importGames(string $externalSourceName)
//    {
//        $externalSourcImpl = $this->externalSourceFactory->createByName($externalSourceName);
//        $gameRepos = $this->container->get(GameRepository::class);
//        $gameScoreRepos = $this->container->get(GameScoreRepository::class);
//        $competitorRepos = $this->container->get(CompetitorRepository::class);
//        $structureRepos = $this->container->get(StructureRepository::class);
//        $gameAttacherRepos = $this->container->get(GameAttacherRepository::class);
//        $competitionAttacherRepos = $this->container->get(CompetitionAttacherRepository::class);
//        $competitorAttacherRepos = $this->container->get(CompetitorAttacherRepository::class);
//
//        $this->importService->importGames(
//            $externalSourcImpl,
//            $gameRepos,
//            $gameScoreRepos,
//            $competitorRepos,
//            $structureRepos,
//            $gameAttacherRepos,
//            $competitionAttacherRepos,
//            $competitorAttacherRepos
//        );
//    }
//
//    /**
//     * @param array|string[] $externalSourceNames
//     */
//    protected function importLayBacks(array $externalSourceNames)
//    {
//        foreach ($externalSourceNames as $externalSourceName) {
//            $externalSourcImpl = $this->externalSourceFactory->createByName($externalSourceName);
//            $gameRepos = $this->container->get(GameRepository::class);
//            $layBackRepos = $this->container->get(LayBackRepository::class);
//            $betLineRepos = $this->container->get(BetLineRepository::class);
//            $bookmakerAttacherRepos = $this->container->get(BookmakerAttacherRepository::class);
//            $competitorAttacherRepos = $this->container->get(CompetitorAttacherRepository::class);
//            $competitionAttacherRepos = $this->container->get(CompetitionAttacherRepository::class);
//
//            $this->importService->importLayBacks(
//                $externalSourcImpl,
//                $gameRepos,
//                $layBackRepos,
//                $betLineRepos,
//                $bookmakerAttacherRepos,
//                $competitorAttacherRepos,
//                $competitionAttacherRepos
//            );
//        }
//    }
}
