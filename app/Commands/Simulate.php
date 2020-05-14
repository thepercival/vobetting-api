<?php

namespace App\Commands;

use DateTime;
use DateTimeImmutable;
use Exception;
use LucidFrame\Console\ConsoleTable;
use Psr\Container\ContainerInterface;
use App\Command;
use Selective\Config\Configuration;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use VOBetting\Transaction;
use VOBetting\Wallet;
use Voetbal\Game;
use Voetbal\NameService;
use Voetbal\Range;
use VOBetting\LayBack\Strategy as LayBackStrategy;

/*
    moment to buy: exchange-lay 1% below bookmaker back and not yet bought and 2 days before start game, games should be beofre now
    moment to sell: (something to sell and 3% profit on back) or, less than 30 minutes before game
*/

// ik zou er verschillende strategieen op los kunnen laten, dit is een prematch strategie!!

// iets van een wallet bijhouden,
// a wat gekocht voor welke wedstrijd voor welke strategie
// b wat verkocht voor welke wedstrijd voor welke strategie

class Simulate extends Command
{
    /**
     * @var ContainerInterface
     */
    protected $container;
    /**
     * @var DateTimeImmutable|false
     */
    protected $startDate;
    /**
     * @var DateTimeImmutable|false
     */
    protected $endDate;
    /**
     * @var int
     */
    protected $nrOfMinutesPerStep;
    /**
     * @var int
     */
    protected $minCurrencySize;
    /**
     * @var int
     */
    protected $maxCurrencySize;
    /**
     * @var Wallet
     */
    protected $wallet;
    /**
     * @var array|LayBackStrategy[]
     */
    protected $strategies;

    protected const DEFAULT_START_DAYSBACK = 2;
    protected const DEFAULT_NROFMINUTESPERSTEP = 14;
    protected const DEFAULT_MIN_CURRENCY_SIZE = 2;
    protected const DEFAULT_MAX_CURRENCY_SIZE = 10;


/*
wallet
bookmaker-to-compare
start-percentage-lay-under-bookmaker-back(excl. exchange-opslag)
winst-percentage-back(excl. exchange-opslag)*/

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        parent::__construct($container->get(Configuration::class));
        $this->strategies = array(
            new LayBackStrategy()
        );
    }

    protected function configure()
    {
        $this
            // the name of the command (the part after "bin/console")
            ->setName('app:simulate')
            // the short description shown while running "php bin/console list"
            ->setDescription('simulate strategies')
            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('simulate strategies');

        $this->addOption('startdate', null, InputOption::VALUE_OPTIONAL, 'format is Y-m-d, defaults to ' . self::DEFAULT_START_DAYSBACK . ' days back');
        $this->addOption('enddate', null, InputOption::VALUE_OPTIONAL, 'format is Y-m-d, defaults to now');
        $this->addOption('nrofminutesperstep', null, InputOption::VALUE_OPTIONAL, 'defaults to ' . self::DEFAULT_NROFMINUTESPERSTEP);
        $this->addOption('mincurrencysize', null, InputOption::VALUE_OPTIONAL, 'defaults to ' . self::DEFAULT_MIN_CURRENCY_SIZE);
        $this->addOption('maxcurrencysize', null, InputOption::VALUE_OPTIONAL, 'defaults to ' . self::DEFAULT_MAX_CURRENCY_SIZE);

        parent::configure();
    }

    protected function init(InputInterface $input, string $name)
    {
        $this->initLogger($input, $name);
        $this->endDate = new DateTimeImmutable();
        if( $input->getOption('enddate') ) {
            $this->startDate = DateTime::createFromFormat(
                'Y-m-d H:i:s',
                $input->getOption('startdate') . ' 00:00:00'
            );
        }
        $this->startDate = $this->endDate->modify("-" . self::DEFAULT_START_DAYSBACK . " days")->setTime(0,0);
        if( $input->getOption('startdate') ) {
            $this->startDate = DateTime::createFromFormat(
                'Y-m-d H:i:s',
                $input->getOption('startdate') . ' 00:00:00'
            );
        }
        $this->nrOfMinutesPerStep = self::DEFAULT_NROFMINUTESPERSTEP;
        if( $input->getOption('nrofminutesperstep') ) {
            $this->nrOfMinutesPerStep = (int) $input->getOption('nrofminutesperstep');
        }

        $this->minCurrencySize = self::DEFAULT_MIN_CURRENCY_SIZE;
        if( $input->getOption('mincurrencysize') ) {
            $this->minCurrencySize = (int) $input->getOption('mincurrencysize');
        }

        $this->maxCurrencySize = self::DEFAULT_MAX_CURRENCY_SIZE;
        if( $input->getOption('maxcurrencysize') ) {
            $this->maxCurrencySize = (int) $input->getOption('maxcurrencysize');
        }

        $this->wallet = new Wallet( new Range( $this->minCurrencySize, $this->maxCurrencySize));
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->init($input, 'cron-simulate');

        $dateIt = clone $this->startDate;
        while ( $dateIt < $this->endDate ) {
            $buyTransactions = $this->buyLayBacks( $dateIt );
            $paidTransactions = $this->wallet->checkPayouts();
            if( count($buyTransactions) > 0 or count($paidTransactions) > 0 ) {
                $this->showWallet($dateIt);
            }
            $dateIt = $dateIt->modify("+" . $this->nrOfMinutesPerStep  . " minutes");
        }
        echo PHP_EOL;
        return 0;
    }

    /**
     * @param DateTimeImmutable $dateTime
     * @return array|Transaction[]
     */
    protected function buyLayBacks( DateTimeImmutable $dateTime): array {
        $transactions = [];
        foreach( $this->strategies as $strategy ) {
            foreach( $strategy->getLayBacks($dateTime) as $layBack ) {
                try {
                    $transactions[] = $this->wallet->buy( $layBack );
                } catch( Exception $e ) {
                    // could be that max amount is exceeded
                }
            }
        }
        return $transactions;
    }

    protected function showWallet( DateTimeImmutable $dateTime ){
        $nameService = new NameService();
        $table = new ConsoleTable();
        $table->setHeaders(array( $dateTime->format("Y-m-d H:i:s"), 'thuis', 'uit', 'odds', 'inzet', $this->wallet->getAmount(), ));
        foreach( $this->wallet->getTransactions() as $transaction ) {
            $layBack = $transaction->getLayBack();
            $game = $layBack->getBetLine()->getGame();
            $row = array(
                $game->getStartDateTime()->format("Y-m-d H:i:s"),
                $nameService->getPlacesFromName($game->getPlaces(Game::HOME), true, true),
                $nameService->getPlacesFromName($game->getPlaces(Game::AWAY), true, true),
                $layBack->getPrice(),
                $transaction->getSize(),
                $transaction->getPayout()
            );
            $table->addRow( $row );
        }
        $table->display();
    }
}
