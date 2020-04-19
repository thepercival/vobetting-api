<?php

declare(strict_types=1);

use DI\ContainerBuilder;
use Psr\Container\ContainerInterface;

use Voetbal\ExternalSource\Repository as ExternalSourceRepository;
use Voetbal\ExternalSource;
use Voetbal\CacheItemDb\Repository as CacheItemDbRepository;
use Voetbal\CacheItemDb;

use VOBetting\Bookmaker\Repository as BookmakerRepository;
use VOBetting\Bookmaker;
use VOBetting\BetLine\Repository as BetLineRepository;
use VOBetting\BetLine;
use VOBetting\LayBack\Repository as LayBackRepository;
use VOBetting\LayBack;

use VOBetting\Attacher\Bookmaker\Repository as BookmakerAttacherRepository;
use VOBetting\Attacher\Bookmaker as BookmakerAttacher;

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
use Voetbal\Game\Repository as GameRepository;
use Voetbal\Game;
use Voetbal\Game\Score\Repository as GameScoreRepository;
use Voetbal\Game\Score as GameScore;

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

return [
    BookmakerRepository::class => function (ContainerInterface $container) {
        $entityManager = $container->get(\Doctrine\ORM\EntityManager::class);
        return new BookmakerRepository($entityManager, $entityManager->getClassMetaData(Bookmaker::class));
    },
    BookmakerAttacherRepository::class => function (ContainerInterface $container) {
        $entityManager = $container->get(\Doctrine\ORM\EntityManager::class);
        return new BookmakerAttacherRepository($entityManager, $entityManager->getClassMetaData(BookmakerAttacher::class));
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
    }
];
