<?php

declare(strict_types=1);

use DI\ContainerBuilder;
use Psr\Container\ContainerInterface;

use VOBetting\Bookmaker\Repository as BookmakerRepository;
use VOBetting\Bookmaker;
use VOBetting\BetLine\Repository as BetLineRepository;
use VOBetting\BetLine;
use VOBetting\LayBack\Repository as LayBackRepository;
use VOBetting\LayBack;

use Voetbal\ExternalSource\Repository as ExternalSourceRepository;
use Voetbal\ExternalSource;

use Voetbal\CacheItemDb\Repository as CacheItemDbRepository;
use Voetbal\CacheItemDb;

use Voetbal\Attacher\Sport\Repository as SportAttacherRepository;
use Voetbal\Attacher\Sport as SportAttacher;
use Voetbal\Attacher\Association\Repository as AssociationAttacherRepository;
use Voetbal\Attacher\Association as AssociationAttacher;
use Voetbal\Attacher\Season\Repository as SeasonAttacherRepository;
use Voetbal\Attacher\Season as SeasonAttacher;
use Voetbal\Attacher\League\Repository as LeagueAttacherRepository;
use Voetbal\Attacher\League as LeagueAttacher;
use Voetbal\Attacher\Competition\Repository as CompetitionAttacherRepository;
use Voetbal\Attacher\Competition as CompetitionAttacher;
use Voetbal\Attacher\Competitor\Repository as CompetitorAttacherRepository;
use Voetbal\Attacher\Competitor as CompetitorAttacher;
use Voetbal\Attacher\Game\Repository as GameAttacherRepository;
use Voetbal\Attacher\Game as GameAttacher;

use Voetbal\Sport\Repository as SportRepository;
use Voetbal\Sport;
use Voetbal\Association\Repository as AssociationRepository;
use Voetbal\Association;
use Voetbal\Season\Repository as SeasonRepository;
use Voetbal\Season;
use Voetbal\League\Repository as LeagueRepository;
use Voetbal\League;
use Voetbal\Competition\Repository as CompetitionRepository;
use Voetbal\Competition;
use Voetbal\Competitor\Repository as CompetitorRepository;
use Voetbal\Competitor;

use Voetbal\Structure\Repository as StructureRepository;
use Voetbal\Planning\Repository as PlanningRepository;
use Voetbal\Planning;
use Voetbal\Planning\Input\Repository as PlanningInputRepository;
use Voetbal\Planning\Input as PlanningInput;
use Voetbal\Planning\Config\Repository as PlanningConfigRepository;
use Voetbal\Planning\Config as PlanningConfig;
use Voetbal\Game\Repository as GameRepository;
use Voetbal\Game;
use Voetbal\Field\Repository as FieldRepository;
use Voetbal\Field;
use Voetbal\Referee\Repository as RefereeRepository;
use Voetbal\Referee;
use Voetbal\Sport\Config\Repository as SportConfigRepository;
use Voetbal\Sport\Config as SportConfig;
use Voetbal\Sport\ScoreConfig\Repository as SportScoreConfigRepository;
use Voetbal\Sport\ScoreConfig as SportScoreConfig;
use Voetbal\Place\Repository as PlaceRepository;
use Voetbal\Place;
use Voetbal\Poule\Repository as PouleRepository;
use Voetbal\Poule;
use Voetbal\Game\Score\Repository as GameScoreRepository;
use Voetbal\Game\Score as GameScore;

return [
    BookmakerRepository::class => function (ContainerInterface $container) {
        $entityManager = $container->get(\Doctrine\ORM\EntityManager::class);
        return new BookmakerRepository($entityManager, $entityManager->getClassMetaData(Bookmaker::class));
    },
    BetLineRepository::class => function (ContainerInterface $container) {
        $entityManager = $container->get(\Doctrine\ORM\EntityManager::class);
        return new BetLineRepository($entityManager, $entityManager->getClassMetaData(BetLine::class));
    },
    LayBackRepository::class => function (ContainerInterface $container) {
        $entityManager = $container->get(\Doctrine\ORM\EntityManager::class);
        return new LayBackRepository($entityManager, $entityManager->getClassMetaData(LayBack::class));
    },
    SportRepository::class => function (ContainerInterface $container) {
        $entityManager = $container->get(\Doctrine\ORM\EntityManager::class);
        return new SportRepository($entityManager, $entityManager->getClassMetaData(Sport::class));
    },
    SportAttacherRepository::class => function (ContainerInterface $container) {
        $entityManager = $container->get(\Doctrine\ORM\EntityManager::class);
        return new SportAttacherRepository($entityManager, $entityManager->getClassMetaData(SportAttacher::class));
    },
    AssociationRepository::class => function (ContainerInterface $container) {
        $entityManager = $container->get(\Doctrine\ORM\EntityManager::class);
        return new AssociationRepository($entityManager, $entityManager->getClassMetaData(Association::class));
    },
    AssociationAttacherRepository::class => function (ContainerInterface $container) {
        $entityManager = $container->get(\Doctrine\ORM\EntityManager::class);
        return new AssociationAttacherRepository($entityManager, $entityManager->getClassMetaData(AssociationAttacher::class));
    },
    SeasonRepository::class => function (ContainerInterface $container) {
        $entityManager = $container->get(\Doctrine\ORM\EntityManager::class);
        return new SeasonRepository($entityManager, $entityManager->getClassMetaData(Season::class));
    },
    SeasonAttacherRepository::class => function (ContainerInterface $container) {
        $entityManager = $container->get(\Doctrine\ORM\EntityManager::class);
        return new SeasonAttacherRepository($entityManager, $entityManager->getClassMetaData(SeasonAttacher::class));
    },
    LeagueRepository::class => function (ContainerInterface $container) {
        $entityManager = $container->get(\Doctrine\ORM\EntityManager::class);
        return new LeagueRepository($entityManager, $entityManager->getClassMetaData(League::class));
    },
    LeagueAttacherRepository::class => function (ContainerInterface $container) {
        $entityManager = $container->get(\Doctrine\ORM\EntityManager::class);
        return new LeagueAttacherRepository($entityManager, $entityManager->getClassMetaData(LeagueAttacher::class));
    },
    CompetitionRepository::class => function (ContainerInterface $container) {
        $entityManager = $container->get(\Doctrine\ORM\EntityManager::class);
        return new CompetitionRepository($entityManager, $entityManager->getClassMetaData(Competition::class));
    },
    CompetitionAttacherRepository::class => function (ContainerInterface $container) {
        $entityManager = $container->get(\Doctrine\ORM\EntityManager::class);
        return new CompetitionAttacherRepository($entityManager, $entityManager->getClassMetaData(CompetitionAttacher::class));
    },
    CompetitorRepository::class => function (ContainerInterface $container) {
        $entityManager = $container->get(\Doctrine\ORM\EntityManager::class);
        return new CompetitorRepository($entityManager, $entityManager->getClassMetaData(Competitor::class));
    },
    CompetitorAttacherRepository::class => function (ContainerInterface $container) {
        $entityManager = $container->get(\Doctrine\ORM\EntityManager::class);
        return new CompetitorAttacherRepository($entityManager, $entityManager->getClassMetaData(CompetitorAttacher::class));
    },
    ExternalSourceRepository::class => function (ContainerInterface $container) {
        $entityManager = $container->get(\Doctrine\ORM\EntityManager::class);
        return new ExternalSourceRepository($entityManager, $entityManager->getClassMetaData(ExternalSource::class));
    },
    CacheItemDbRepository::class => function (ContainerInterface $container) {
        $entityManager = $container->get(\Doctrine\ORM\EntityManager::class);
        return new CacheItemDbRepository($entityManager, $entityManager->getClassMetaData(CacheItemDb::class));
    },

    /*SportRepository::class => function (ContainerInterface $container) {
        $entityManager = $container->get(\Doctrine\ORM\EntityManager::class);
        return new SportRepository($entityManager, $entityManager->getClassMetaData(Sport::class));
    },
    SeasonRepository::class => function (ContainerInterface $container) {
        $entityManager = $container->get(\Doctrine\ORM\EntityManager::class);
        return new SeasonRepository($entityManager, $entityManager->getClassMetaData(Season::class));
    },
    LeagueRepository::class => function (ContainerInterface $container) {
        $entityManager = $container->get(\Doctrine\ORM\EntityManager::class);
        return new LeagueRepository($entityManager, $entityManager->getClassMetaData(League::class));
    },
    CompetitionRepository::class => function (ContainerInterface $container) {
        $entityManager = $container->get(\Doctrine\ORM\EntityManager::class);
        return new CompetitionRepository($entityManager, $entityManager->getClassMetaData(Competition::class));
    },

    StructureRepository::class => function (ContainerInterface $container) {
        $entityManager = $container->get(\Doctrine\ORM\EntityManager::class);
        return new StructureRepository($entityManager);
    },
    PlanningRepository::class => function (ContainerInterface $container) {
        $entityManager = $container->get(\Doctrine\ORM\EntityManager::class);
        return new PlanningRepository($entityManager, $entityManager->getClassMetaData(Planning::class));
    },
    PlanningInputRepository::class => function (ContainerInterface $container) {
        $entityManager = $container->get(\Doctrine\ORM\EntityManager::class);
        return new PlanningInputRepository(
            $entityManager,
            $entityManager->getClassMetaData(PlanningInput::class)
        );
    },*/
    GameRepository::class => function (ContainerInterface $container) {
        $entityManager = $container->get(\Doctrine\ORM\EntityManager::class);
        return new GameRepository($entityManager, $entityManager->getClassMetaData(Game::class));
    },
    GameAttacherRepository::class => function (ContainerInterface $container) {
        $entityManager = $container->get(\Doctrine\ORM\EntityManager::class);
        return new GameAttacherRepository($entityManager, $entityManager->getClassMetaData(GameAttacher::class));
    },
    GameScoreRepository::class => function (ContainerInterface $container) {
        $entityManager = $container->get(\Doctrine\ORM\EntityManager::class);
        return new GameScoreRepository($entityManager, $entityManager->getClassMetaData(GameScore::class));
    }/*,
    FieldRepository::class => function (ContainerInterface $container) {
        $entityManager = $container->get(\Doctrine\ORM\EntityManager::class);
        return new FieldRepository($entityManager, $entityManager->getClassMetaData(Field::class));
    },
    RefereeRepository::class => function (ContainerInterface $container) {
        $entityManager = $container->get(\Doctrine\ORM\EntityManager::class);
        return new RefereeRepository($entityManager, $entityManager->getClassMetaData(Referee::class));
    },
    SportConfigRepository::class => function (ContainerInterface $container) {
        $entityManager = $container->get(\Doctrine\ORM\EntityManager::class);
        return new SportConfigRepository($entityManager, $entityManager->getClassMetaData(SportConfig::class));
    },
    CompetitorRepository::class => function (ContainerInterface $container) {
        $entityManager = $container->get(\Doctrine\ORM\EntityManager::class);
        return new CompetitorRepository($entityManager, $entityManager->getClassMetaData(Competitor::class));
    },
    SportScoreConfigRepository::class => function (ContainerInterface $container) {
        $entityManager = $container->get(\Doctrine\ORM\EntityManager::class);
        return new SportScoreConfigRepository(
            $entityManager,
            $entityManager->getClassMetaData(SportScoreConfig::class)
        );
    },
    PouleRepository::class => function (ContainerInterface $container) {
        $entityManager = $container->get(\Doctrine\ORM\EntityManager::class);
        return new PouleRepository($entityManager, $entityManager->getClassMetaData(Poule::class));
    },
    PlaceRepository::class => function (ContainerInterface $container) {
        $entityManager = $container->get(\Doctrine\ORM\EntityManager::class);
        return new PlaceRepository($entityManager, $entityManager->getClassMetaData(Place::class));
    },
    PlanningConfigRepository::class => function (ContainerInterface $container) {
        $entityManager = $container->get(\Doctrine\ORM\EntityManager::class);
        return new PlanningConfigRepository($entityManager, $entityManager->getClassMetaData(PlanningConfig::class));
    },*/
];
