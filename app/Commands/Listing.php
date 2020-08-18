<?php

namespace App\Commands;

use DateTimeImmutable;
use Psr\Container\ContainerInterface;
use App\Command;
use Selective\Config\Configuration;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use VOBetting\Attacher\Bookmaker\Repository as BookmakerAttacherRepository;
use VOBetting\BetLine\Repository as BetLineRepository;
use VOBetting\ExternalSource\Matchbook;
use VOBetting\ExternalSource\TheOddsApi;
use VOBetting\LayBack\Repository as LayBackRepository;
use SportsImport\Attacher\Game\Repository as GameAttacherRepository;
use SportsImport\Attacher\Place\Repository as PlaceAttacherRepository;
use SportsImport\Attacher\Poule\Repository as PouleAttacherRepository;
use SportsImport\Attacher\Sport\Repository as SportAttacherRepository;
use SportsImport\Attacher\Association\Repository as AssociationAttacherRepository;
use SportsImport\Attacher\League\Repository as LeagueAttacherRepository;
use SportsImport\Attacher\Season\Repository as SeasonAttacherRepository;
use SportsImport\Attacher\Competition\Repository as CompetitionAttacherRepository;
use SportsImport\Attacher\Competitor\Repository as CompetitorAttacherRepository;

use VOBetting\ExternalSource\Factory as ExternalSourceFactory;
use Sports\ExternalSource\SofaScore;
use VOBetting\ExternalSource\Betfair;
use Sports\Game\Repository as GameRepository;
use Sports\Game\Score\Repository as GameScoreRepository;
use VOBetting\Import\Service as ImportService;
use Sports\Sport\Repository as SportRepository;
use Sports\Association\Repository as AssociationRepository;
use Sports\League\Repository as LeagueRepository;
use Sports\Season\Repository as SeasonRepository;
use Sports\Competition\Repository as CompetitionRepository;
use Sports\Competitor\Repository as CompetitorRepository;
use Sports\Structure\Repository as StructureRepository;

class Listing extends Command
{
    /**
     * @var array|string[]
     */
    protected $commandKeys;
    /**
     * @var ContainerInterface
     */
    protected $container;

    public function __construct(ContainerInterface $container, array $commandKeys)
    {
        $this->container = $container;
        $this->commandKeys = $commandKeys;
        parent::__construct($container->get(Configuration::class));
    }

    protected function configure()
    {
        $this
            // the name of the command (the part after "bin/console")
            ->setName('app:list')
            // the short description shown while running "php bin/console list"
            ->setDescription('list the commands')
            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('list the commands');

        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        foreach( $this->commandKeys as $commandKey ) {

            /** @var Command $command */
            $command = $this->container->get($commandKey);
            echo $commandKey . " (" . $command->getDescription() . ")" . PHP_EOL;
            foreach( $command->getDefinition()->getArguments() as $argument ) {
                echo "  " . $argument->getName() . " (" . $argument->getDescription() . ")" . PHP_EOL;
            }
            foreach( $command->getDefinition()->getOptions() as $option ) {
                echo " --" . $option->getName() . " (" . $option->getDescription() . ")" . PHP_EOL;
            }
            echo PHP_EOL;
        }
        return 0;
    }

}
