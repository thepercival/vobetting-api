<?php

namespace App\Commands;

use DateTimeImmutable;
use Exception;
use League\Period\Period;
use LucidFrame\Console\ConsoleTable;
use Psr\Container\ContainerInterface;
use App\Command;
use Selective\Config\Configuration;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use VOBetting\BetLine\Repository as BetLineRepository;
use VOBetting\LayBack\Repository as LayBackRepository;
use VOBetting\Bookmaker\Repository as BookmakerRepository;
use VOBetting\Transaction;
use VOBetting\Wallet;
use Voetbal\Game;
use Voetbal\NameService;
use Voetbal\Range;
use VOBetting\Strategy;
use VOBetting\Strategy\PreMatchPriceGoingUp;
use Voetbal\Sport\Repository as SportRepository;

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
    protected const DEFAULT_BUY_HOURS_IN_PAST_START = 2;
    protected const DEFAULT_BUY_HOURS_IN_PAST_END = 24 * 14;
    protected const DEFAULT_BASELINE_DELTA_PERCENTAGE = 0;

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

        $this->addArgument('daysinpaststart', InputArgument::OPTIONAL, 'defaults to ' . self::DEFAULT_DAYS_IN_PAST_START );
        $this->addArgument('daysinpastend', InputArgument::OPTIONAL, 'defaults to ' . self::DEFAULT_DAYS_IN_PAST_END );
        $this->addArgument('nrofminutesperstep', InputArgument::OPTIONAL, 'defaults to ' . self::DEFAULT_NROFMINUTESPERSTEP);
        $this->addArgument('mincurrencysize', InputArgument::OPTIONAL, 'defaults to ' . self::DEFAULT_MIN_CURRENCY_SIZE);
        $this->addArgument('maxcurrencysize', InputArgument::OPTIONAL, 'defaults to ' . self::DEFAULT_MAX_CURRENCY_SIZE);
        // strategy
        $this->addArgument('buyperiodinhours', InputArgument::OPTIONAL, 'format is ' . self::DEFAULT_BUY_HOURS_IN_PAST_START . '->' . self::DEFAULT_BUY_HOURS_IN_PAST_END );
        $this->addArgument('baselinedeltapercentage', InputArgument::OPTIONAL, 'defaults to ' . self::DEFAULT_BASELINE_DELTA_PERCENTAGE );
        $this->addArgument('baselinebookmakernames', InputArgument::OPTIONAL, 'format is comma-separate-list of bookmaker-names, defaults to nothing(average) ' . self::DEFAULT_BASELINE_DELTA_PERCENTAGE );

        parent::configure();
    }

    protected function init(InputInterface $input, string $name)
    {
        $this->initLogger($input, $name);
        $this->endDate = new DateTimeImmutable();

        $now = new DateTimeImmutable();
        $daysInPastStart = self::DEFAULT_DAYS_IN_PAST_START;
        if( $input->getArgument('daysinpaststart') !== null ) {
            $daysInPastStart = (int) $input->getArgument('daysinpaststart');
        }
        $this->startDate = $now->modify("-".$daysInPastStart." days");

        $daysInPastEnd = self::DEFAULT_DAYS_IN_PAST_END;
        if( $input->getArgument('daysinpastend') !== null ) {
            $daysInPastEnd = (int) $input->getArgument('daysinpastend');
        }
        $this->endDate = $now->modify("-".$daysInPastEnd." days");

        $this->nrOfMinutesPerStep = self::DEFAULT_NROFMINUTESPERSTEP;
        if( $input->getArgument('nrofminutesperstep') !== null ) {
            $this->nrOfMinutesPerStep = (int) $input->getArgument('nrofminutesperstep');
        }

        $this->minCurrencySize = self::DEFAULT_MIN_CURRENCY_SIZE;
        if( $input->getArgument('mincurrencysize') !== null ) {
            $this->minCurrencySize = (int) $input->getArgument('mincurrencysize');
        }

        $this->maxCurrencySize = self::DEFAULT_MAX_CURRENCY_SIZE;
        if( $input->getArgument('maxcurrencysize') !== null ) {
            $this->maxCurrencySize = (int) $input->getArgument('maxcurrencysize');
        }

        $this->wallet = new Wallet( new Range( $this->minCurrencySize, $this->maxCurrencySize));

        $this->initStrategy( $input );

    }

    protected function initStrategy(InputInterface $input)
    {
        $buyHoursInPastStart = self::DEFAULT_BUY_HOURS_IN_PAST_START;
        $buyHoursInPastEnd = self::DEFAULT_BUY_HOURS_IN_PAST_END;
        if( $input->getArgument('buyperiodinhours') !== null ) {
            $strPos = strpos( $input->getArgument('buyperiodinhours'), "=>" );
            if( $strPos !== false ) {
                $buyHoursInPastStart = substr( $input->getArgument('buyperiodinhours'), 0, $strPos );
                $buyHoursInPastEnd = substr( $input->getArgument('buyperiodinhours'), $strPos + strlen("=>") );
            }
        }
        $buyPeriodInHours = new Range( $buyHoursInPastStart, $buyHoursInPastEnd );

        $baselineDeltaPercentage = self::DEFAULT_BASELINE_DELTA_PERCENTAGE;
        if( $input->getArgument('baselinedeltapercentage') !== null ) {
            $baselineDeltaPercentage = (int) $input->getArgument('baselinedeltapercentage');
        }

        $baselineBookmakers = [];
        if( $input->getArgument('baselinebookmakernames') !== null ) {
            $bookmakerRepos = $this->container->get(BookmakerRepository::class);
            $baselineBookmakerNames = explode(",", $input->getArgument('baselinebookmakernames') );
            foreach( $baselineBookmakerNames as $baselineBookmakerName ) {
                $bookmaker = $bookmakerRepos->findOneBy( ["name" => $baselineBookmakerName ]);
                if( $bookmaker !== null ) {
                    $baselineBookmakers[] = $bookmaker;
                }
            }
        }

        $this->strategies = array(
            new PreMatchPriceGoingUp(
                $this->container->get(BetLineRepository::class),
                $this->container->get(LayBackRepository::class),
                $buyPeriodInHours,
                $baselineBookmakers,
                $baselineDeltaPercentage )
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->init($input, 'cron-simulate');

        $dateIt = clone $this->startDate;
        while ( $dateIt < $this->endDate ) {
            $period = new Period( $dateIt->modify("-" . $this->nrOfMinutesPerStep  . " minutes"), $dateIt );
            $buyTransactions = $this->buyLayBacks( $period );
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
     * @param Period $period
     * @return array|Transaction[]
     */
    protected function buyLayBacks( Period $period): array {
        $transactions = [];
        foreach( $this->strategies as $strategy ) {
            foreach( $strategy->getLayBackCandidates($period) as $layBack ) {
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
