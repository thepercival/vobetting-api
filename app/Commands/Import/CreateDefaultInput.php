<?php

namespace App\Commands\Import;

use Psr\Container\ContainerInterface;
use App\Command;
use Selective\Config\Configuration;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Voetbal\Planning\Input;
use Voetbal\Planning\Input\Repository as PlanningInputRepository;

use Voetbal\Planning\Input\Service as PlanningInputService;
use Voetbal\Planning\Input\Iterator as PlanningInputIterator;
use Voetbal\Range as VoetbalRange;
use Voetbal\Structure\Options as StructureOptions;

class CreateDefaultInput extends Command
{
    /**
     * @var PlanningInputRepository
     */
    protected $planningInputRepos;
    /**
     * @var PlanningInputService
     */
    protected $planningInputSerivce;

    public function __construct(ContainerInterface $container)
    {
        // $settings = $container->get('settings');
        $this->planningInputRepos = $container->get(PlanningInputRepository::class);
        $this->planningInputSerivce = new PlanningInputService();
        parent::__construct($container->get(Configuration::class));
    }

    protected function configure()
    {
        $this
            // the name of the command (the part after "bin/console")
            ->setName('app:create-default-planning-input')
            // the short description shown while running "php bin/console list"
            ->setDescription('Creates the default planning-inputs')
            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('Creates the default planning-inputs');
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->initLogger($input, 'cron-planning-create-default-input');
        return $this->createPlanningInputs();
    }

    protected function createPlanningInputs(): int
    {
        $structureOptions = new StructureOptions(
            new VoetbalRange(1, 10), // poules
            new VoetbalRange(2, 20), // places
            new VoetbalRange(2, 10)
        );

        $planningInputIterator = new PlanningInputIterator(
            $structureOptions,
            new VoetbalRange(1, 1), // sports
            new VoetbalRange(1, 10),// fields
            new VoetbalRange(0, 10),// referees
            new VoetbalRange(1, 2),// headtohead
        );

        while ($planningInput = $planningInputIterator->increment()) {
            $this->logger->info($this->inputToString($planningInput));

//            if(  $planningInput->getNrOfPlaces() === 20 && $planningInput->getNrOfPoules() === 2
//                && $planningInput->getNrOfFields() === 2
//                && $planningInput->getNrOfReferees() === 0
//                && $planningInput->getTeamup() === false  && $planningInput->getSelfReferee() === true
//                && $planningInput->getNrOfHeadtohead() === 2 ) {
//                $x = 2;
//            }

            if ($this->planningInputRepos->getFromInput($planningInput) === null) {
                $this->planningInputRepos->save($planningInput);
            }
        }
        return 0;
    }

    protected function inputToString(Input $planningInput): string
    {
        $sports = array_map(
            function (array $sportConfig) {
                return '' . $sportConfig["nrOfFields"];
            },
            $planningInput->getSportConfig()
        );
        return 'structure [' . implode('|', $planningInput->getStructureConfig()) . ']'
            . ', sports [' . implode(',', $sports) . ']'
            . ', referees ' . $planningInput->getNrOfReferees()
            . ', teamup ' . ($planningInput->getTeamup() ? '1' : '0')
            . ', selfRef ' . ($planningInput->getSelfReferee() ? '1' : '0')
            . ', nrOfH2h ' . $planningInput->getNrOfHeadtohead();
    }


//    protected function addInput(
//        array $structureConfig,
//        array $sportConfig,
//        int $nrOfReferees,
//        int $nrOfFields,
//        bool $teamup,
//        bool $selfReferee,
//        int $nrOfHeadtohead
//    ) {
//        /*if ($nrOfCompetitors === 6 && $nrOfPoules === 1 && $nrOfSports === 1 && $nrOfFields === 2
//            && $nrOfReferees === 0 && $nrOfHeadtohead === 1 && $teamup === false && $selfReferee === false ) {
//            $w1 = 1;
//        } else*/ /*if ($nrOfCompetitors === 12 && $nrOfPoules === 2 && $nrOfSports === 1 && $nrOfFields === 4
//            && $nrOfReferees === 0 && $nrOfHeadtohead === 1 && $teamup === false && $selfReferee === false ) {
//            $w1 = 1;
//        } else {
//            continue;
//        }*/
//
//        $multipleSports = count($sportConfig) > 1;
//        $newNrOfHeadtohead = $nrOfHeadtohead;
//        if ($multipleSports) {
//            //                                    if( count($sportConfig) === 4 && $sportConfig[0]["nrOfFields"] == 1 && $sportConfig[1]["nrOfFields"] == 1
//            //                                        && $sportConfig[2]["nrOfFields"] == 1 && $sportConfig[3]["nrOfFields"] == 1
//            //                                        && $teamup === false && $selfReferee === false && $nrOfHeadtohead === 1 && $structureConfig == [3]  ) {
//            //                                        $e = 2;
//            //                                    }
//            $newNrOfHeadtohead = $this->planningInputSerivce->getSufficientNrOfHeadtohead(
//                $nrOfHeadtohead,
//                min($structureConfig),
//                $teamup,
//                $selfReferee,
//                $sportConfig
//            );
//        }
//        $planningInput = $this->planningInputRepos->get(
//            $structureConfig,
//            $sportConfig,
//            $nrOfReferees,
//            $teamup,
//            $selfReferee,
//            $newNrOfHeadtohead
//        );
//        if ($planningInput !== null) {
//            return;
//        }
//        $planningInput = new PlanningInput(
//            $structureConfig,
//            $sportConfig,
//            $nrOfReferees,
//            $teamup,
//            $selfReferee,
//            $newNrOfHeadtohead
//        );
//
//        if (!$multipleSports) {
//            $maxNrOfFieldsInPlanning = $planningInput->getMaxNrOfBatchGames(
//                Resources::REFEREES + Resources::PLACES
//            );
//            if ($nrOfFields > $maxNrOfFieldsInPlanning) {
//                return;
//            }
//        } else {
//            if ($nrOfFields > self::MAXNROFFIELDS_FOR_MULTIPLESPORTS) {
//                return;
//            }
//        }
//
//        $maxNrOfRefereesInPlanning = $planningInput->getMaxNrOfBatchGames(
//            Resources::FIELDS + Resources::PLACES
//        );
//        if ($nrOfReferees > $maxNrOfRefereesInPlanning) {
//            return;
//        }
//
//        $this->planningInputRepos->save($planningInput);
//        // die();
//    }
}
