<?php

namespace App\Commands;

use DateTimeImmutable;
use Exception;
use League\Period\Period;
use LucidFrame\Console\ConsoleTable;
use Psr\Container\ContainerInterface;
use App\Command;
use Selective\Config\Configuration;

use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use VOBetting\BetLine\Repository as BetLineRepository;
use VOBetting\LayBack\Repository as LayBackRepository;
use VOBetting\Bookmaker\Repository as BookmakerRepository;
use VOBetting\Transaction;
use VOBetting\Transaction\Output as TransactionOutput;
use VOBetting\Wallet;
use Sports\Game;
use Sports\NameService;
use SportsHelpers\Range;
use VOBetting\Output;
use VOBetting\Strategy;
use VOBetting\Strategy\PreMatchPriceGoingUp;
use Sports\Sport\Repository as SportRepository;

/*
    moment to buy: exchange-lay 1% below bookmaker back and not yet bought and 2 days before start game, games should be before now
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
     * @var array|Strategy[]
     */
    protected $strategies;

    protected const DEFAULT_DAYS_IN_PAST_START = 14;
    protected const DEFAULT_DAYS_IN_PAST_END = 0;
    protected const DEFAULT_NROFMINUTESPERSTEP = 14;
    protected const DEFAULT_MIN_CURRENCY_SIZE = 2;
    protected const DEFAULT_MAX_CURRENCY_SIZE = 10;
    // strategy
    protected const DEFAULT_SELL_HOURS_IN_PAST_START = 24 * 14;
    protected const DEFAULT_SELL_HOURS_IN_PAST_END = 0;
    protected const DEFAULT_BUY_HOURS_IN_PAST_START = 24 * 7;
    protected const DEFAULT_BUY_HOURS_IN_PAST_END = 24 * 3;
    protected const DEFAULT_BASELINE_DELTA_PERCENTAGE = 2;
    protected const DEFAULT_PROFIT_PERCENTAGE = 1;

/*
wallet
bookmaker-to-compare
start-percentage-lay-under-bookmaker-back(excl. exchange-opslag)
winst-percentage-back(excl. exchange-opslag)*/

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        parent::__construct($container->get(Configuration::class));
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

        $this->addOption('daysinpaststart', null, InputOption::VALUE_OPTIONAL, 'defaults to ' . self::DEFAULT_DAYS_IN_PAST_START );
        $this->addOption('daysinpastend', null, InputOption::VALUE_OPTIONAL, 'defaults to ' . self::DEFAULT_DAYS_IN_PAST_END );
        $this->addOption('nrofminutesperstep', null, InputOption::VALUE_OPTIONAL, 'defaults to ' . self::DEFAULT_NROFMINUTESPERSTEP);
        $this->addOption('mincurrencysize', null, InputOption::VALUE_OPTIONAL, 'defaults to ' . self::DEFAULT_MIN_CURRENCY_SIZE);
        $this->addOption('maxcurrencysize', null, InputOption::VALUE_OPTIONAL, 'defaults to ' . self::DEFAULT_MAX_CURRENCY_SIZE);
        // strategy
        $this->addOption('buyperiodinhours', null, InputOption::VALUE_OPTIONAL, 'format is ' . self::DEFAULT_BUY_HOURS_IN_PAST_START . '->' . self::DEFAULT_BUY_HOURS_IN_PAST_END );
        $this->addOption('sellperiodinhours', null, InputOption::VALUE_OPTIONAL, 'format is ' . self::DEFAULT_SELL_HOURS_IN_PAST_START . '->' . self::DEFAULT_SELL_HOURS_IN_PAST_END );
        $this->addOption('baselinebookmakernames', null, InputOption::VALUE_OPTIONAL, 'format is comma-separate-list of bookmaker-names, defaults to nothing(average) ' . self::DEFAULT_BASELINE_DELTA_PERCENTAGE );
        $this->addOption('profitpercentage', null, InputOption::VALUE_OPTIONAL, 'defaults to ' . self::DEFAULT_PROFIT_PERCENTAGE );
        $this->addOption('baselinedeltapercentage', null, InputOption::VALUE_OPTIONAL, 'defaults to ' . self::DEFAULT_BASELINE_DELTA_PERCENTAGE );

        parent::configure();
    }

    protected function init(InputInterface $input, string $name)
    {
        $this->initLogger($input, $name);
        $this->endDate = new DateTimeImmutable();

        $now = new DateTimeImmutable();
        $daysInPastStart = self::DEFAULT_DAYS_IN_PAST_START;
        if( $input->getOption('daysinpaststart') !== null ) {
            $daysInPastStart = (int) $input->getOption('daysinpaststart');
        }
        $this->startDate = $now->modify("-".$daysInPastStart." days");

        $daysInPastEnd = self::DEFAULT_DAYS_IN_PAST_END;
        if( $input->getOption('daysinpastend') !== null ) {
            $daysInPastEnd = (int) $input->getOption('daysinpastend');
        }
        $this->endDate = $now->modify("-".$daysInPastEnd." days");

        $this->nrOfMinutesPerStep = self::DEFAULT_NROFMINUTESPERSTEP;
        if( $input->getOption('nrofminutesperstep') !== null ) {
            $this->nrOfMinutesPerStep = (int) $input->getOption('nrofminutesperstep');
        }

        $this->minCurrencySize = self::DEFAULT_MIN_CURRENCY_SIZE;
        if( $input->getOption('mincurrencysize') !== null ) {
            $this->minCurrencySize = (int) $input->getOption('mincurrencysize');
        }

        $this->maxCurrencySize = self::DEFAULT_MAX_CURRENCY_SIZE;
        if( $input->getOption('maxcurrencysize') !== null ) {
            $this->maxCurrencySize = (int) $input->getOption('maxcurrencysize');
        }

        $this->wallet = new Wallet( new Range( $this->minCurrencySize, $this->maxCurrencySize));

        $this->initStrategy( $input );

    }

    protected function initStrategy(InputInterface $input)
    {
        $buyHoursInPastStart = self::DEFAULT_BUY_HOURS_IN_PAST_START;
        $buyHoursInPastEnd = self::DEFAULT_BUY_HOURS_IN_PAST_END;
        if( $input->getOption('buyperiodinhours') !== null ) {
            $strPos = strpos( $input->getOption('buyperiodinhours'), "=>" );
            if( $strPos !== false ) {
                $buyHoursInPastStart = substr( $input->getOption('buyperiodinhours'), 0, $strPos );
                $buyHoursInPastEnd = substr( $input->getOption('buyperiodinhours'), $strPos + strlen("=>") );
            }
        }
        $buyPeriodInHours = new Range( $buyHoursInPastStart, $buyHoursInPastEnd );

        $sellHoursInPastStart = self::DEFAULT_SELL_HOURS_IN_PAST_START;
        $sellHoursInPastEnd = self::DEFAULT_SELL_HOURS_IN_PAST_END;
        if( $input->getOption('sellperiodinhours') !== null ) {
            $strPos = strpos( $input->getOption('sellperiodinhours'), "=>" );
            if( $strPos !== false ) {
                $sellHoursInPastStart = substr( $input->getOption('sellperiodinhours'), 0, $strPos );
                $sellHoursInPastEnd = substr( $input->getOption('sellperiodinhours'), $strPos + strlen("=>") );
            }
        }
        $sellPeriodInHours = new Range( $sellHoursInPastStart, $sellHoursInPastEnd );

        $baselineBookmakers = [];
        $bookmakerRepos = $this->container->get(BookmakerRepository::class);
        if( $input->getOption('baselinebookmakernames') !== null ) {
            $baselineBookmakerNames = explode(",", $input->getOption('baselinebookmakernames') );
            foreach( $baselineBookmakerNames as $baselineBookmakerName ) {
                $bookmaker = $bookmakerRepos->findOneBy( ["name" => $baselineBookmakerName ]);
                if( $bookmaker !== null ) {
                    $baselineBookmakers[] = $bookmaker;
                }
            }
        } else {
            $baselineBookmakers = $bookmakerRepos->findBy( [ "exchange" => false ] );
        }

        $profitPercentage = self::DEFAULT_PROFIT_PERCENTAGE;
        if( $input->getOption('profitpercentage') !== null ) {
            $profitPercentage = (int) $input->getOption('profitpercentage');
        }

        $baselineDeltaPercentage = self::DEFAULT_BASELINE_DELTA_PERCENTAGE;
        if( $input->getOption('baselinedeltapercentage') !== null ) {
            $baselineDeltaPercentage = (int) $input->getOption('baselinedeltapercentage');
        }

        $this->strategies = array(
            new PreMatchPriceGoingUp(
                $this->container->get(BetLineRepository::class),
                $this->container->get(LayBackRepository::class),
                $buyPeriodInHours,
                $sellPeriodInHours,
                $baselineBookmakers,
                $profitPercentage,
                $baselineDeltaPercentage )
            );
    }


    // in de wallet zitten alle transacties
    // normaliter bestaat 1 succesvolle bet voor pricegoingup uit:
    // 1 : buy transaction => goedkope lay
    // 2 : buy transaction => dure back
    // 3 : payout transactions(2x) => waarbij de een 0 en de ander eerst meer als de som van de 2 buy transactions
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->init($input, 'cron-simulate');

        $dateIt = clone $this->startDate;
        while ( $dateIt < $this->endDate ) {
            $period = new Period( $dateIt->modify("-" . $this->nrOfMinutesPerStep  . " minutes"), $dateIt );
            $this->logger->info("processing period " . $period->getStartDate()->format("Y-m-d H:i") . "  => " . $period->getEndDate()->format("Y-m-d H:i"));
            $buyTransactions = $this->buyLayBacks( $period );
            $payoutTransactions = $this->wallet->checkPayouts($period->getEndDate());
            if( count($buyTransactions) > 0 or count($payoutTransactions) > 0 ) {
                $this->showWallet($dateIt);
            }
            $dateIt = $dateIt->modify("+" . $this->nrOfMinutesPerStep  . " minutes");
        }
        echo PHP_EOL;
        return 0;
    }

    /**
     * @param Period $period
     * @return array|Transaction[]
     */
    protected function buyLayBacks( Period $period): array {
        $transactions = [];
        foreach( $this->strategies as $strategy ) {
            $candidates = $strategy->getLayBackCandidates($period);
            foreach( $candidates as $layBack ) {
                try {
                    $transaction = $this->wallet->buy( $layBack, $period->getEndDate() );
                    if( $transaction === null ) {
                        continue;
                    }
                    $transactions[] = $transaction;
                    $strategy->addTransaction( $transaction );
                } catch( Exception $e ) {
                    // could be that max amount is exceeded
                }
            }
        }
        return $transactions;
    }

    protected function showWallet( DateTimeImmutable $dateTime ){

        // $table->setHeaders(array( $dateTime->format("Y-m-d H:i:s"), 'thuis', 'uit', 'odds', 'inzet', $this->wallet->getAmount(), ));
        //
        $this->logger->info("all transactions of wallet at  " . $dateTime->format(Output::DATEFORMAT ) );
        $transactionOutput = new TransactionOutput($this->wallet->getTransactions());
        $transactionOutput->toConsole();
    }
}
