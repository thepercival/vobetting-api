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
