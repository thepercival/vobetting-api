<?php

declare(strict_types=1);

use DI\ContainerBuilder;
use Psr\Container\ContainerInterface;

use Sports\ExternalSource\Repository as ExternalSourceRepository;
use Sports\ExternalSource;
use Sports\CacheItemDb\Repository as CacheItemDbRepository;
use Sports\CacheItemDb;

use VOBetting\Bookmaker\Repository as BookmakerRepository;
use VOBetting\Bookmaker;
use VOBetting\BetGameRepository;
use VOBetting\BetLine\Repository as BetLineRepository;
use VOBetting\BetLine;
use VOBetting\LayBack\Repository as LayBackRepository;
use VOBetting\LayBack;

use VOBetting\Attacher\Bookmaker\Repository as BookmakerAttacherRepository;
use VOBetting\Attacher\Bookmaker as BookmakerAttacher;

use Sports\Sport\Repository as SportRepository;
use Sports\Sport;
use Sports\Association\Repository as AssociationRepository;
use Sports\Association;
use Sports\Season\Repository as SeasonRepository;
use Sports\Season;
use Sports\League\Repository as LeagueRepository;
use Sports\League;
use Sports\Competition\Repository as CompetitionRepository;
use Sports\Competition;
use Sports\Competitor\Repository as CompetitorRepository;
use Sports\Competitor;
use Sports\Game\Repository as GameRepository;
use Sports\Game;
use Sports\Game\Score\Repository as GameScoreRepository;
use Sports\Game\Score as GameScore;

use SportsImport\Attacher\Sport\Repository as SportAttacherRepository;
use SportsImport\Attacher\Sport as SportAttacher;
use SportsImport\Attacher\Association\Repository as AssociationAttacherRepository;
use SportsImport\Attacher\Association as AssociationAttacher;
use SportsImport\Attacher\Season\Repository as SeasonAttacherRepository;
use SportsImport\Attacher\Season as SeasonAttacher;
use SportsImport\Attacher\League\Repository as LeagueAttacherRepository;
use SportsImport\Attacher\League as LeagueAttacher;
use SportsImport\Attacher\Competition\Repository as CompetitionAttacherRepository;
use SportsImport\Attacher\Competition as CompetitionAttacher;
use SportsImport\Attacher\Competitor\Repository as CompetitorAttacherRepository;
use SportsImport\Attacher\Competitor as CompetitorAttacher;
use SportsImport\Attacher\Game\Repository as GameAttacherRepository;
use SportsImport\Attacher\Game as GameAttacher;

return [
    BookmakerRepository::class => function (ContainerInterface $container): BookmakerRepository {
        $entityManager = $container->get(\Doctrine\ORM\EntityManager::class);
        return new BookmakerRepository($entityManager, $entityManager->getClassMetaData(Bookmaker::class));
    },
    BookmakerAttacherRepository::class => function (ContainerInterface $container): BookmakerAttacherRepository {
        $entityManager = $container->get(\Doctrine\ORM\EntityManager::class);
        return new BookmakerAttacherRepository($entityManager, $entityManager->getClassMetaData(BookmakerAttacher::class));
    },
    BetGameRepository::class => function (ContainerInterface $container): BetGameRepository {
        return new BetGameRepository($container->get(\Doctrine\ORM\EntityManager::class));
    },
    BetLineRepository::class => function (ContainerInterface $container): BetLineRepository {
        $entityManager = $container->get(\Doctrine\ORM\EntityManager::class);
        return new BetLineRepository($entityManager, $entityManager->getClassMetaData(BetLine::class));
    },
    LayBackRepository::class => function (ContainerInterface $container): LayBackRepository {
        $entityManager = $container->get(\Doctrine\ORM\EntityManager::class);
        return new LayBackRepository($entityManager, $entityManager->getClassMetaData(LayBack::class));
    },
    SportRepository::class => function (ContainerInterface $container): SportRepository {
        $entityManager = $container->get(\Doctrine\ORM\EntityManager::class);
        return new SportRepository($entityManager, $entityManager->getClassMetaData(Sport::class));
    },
    SportAttacherRepository::class => function (ContainerInterface $container): SportAttacherRepository {
        $entityManager = $container->get(\Doctrine\ORM\EntityManager::class);
        return new SportAttacherRepository($entityManager, $entityManager->getClassMetaData(SportAttacher::class));
    },
    AssociationRepository::class => function (ContainerInterface $container): AssociationRepository {
        $entityManager = $container->get(\Doctrine\ORM\EntityManager::class);
        return new AssociationRepository($entityManager, $entityManager->getClassMetaData(Association::class));
    },
    AssociationAttacherRepository::class => function (ContainerInterface $container): AssociationAttacherRepository {
        $entityManager = $container->get(\Doctrine\ORM\EntityManager::class);
        return new AssociationAttacherRepository($entityManager, $entityManager->getClassMetaData(AssociationAttacher::class));
    },
    SeasonRepository::class => function (ContainerInterface $container): SeasonRepository {
        $entityManager = $container->get(\Doctrine\ORM\EntityManager::class);
        return new SeasonRepository($entityManager, $entityManager->getClassMetaData(Season::class));
    },
    SeasonAttacherRepository::class => function (ContainerInterface $container): SeasonAttacherRepository {
        $entityManager = $container->get(\Doctrine\ORM\EntityManager::class);
        return new SeasonAttacherRepository($entityManager, $entityManager->getClassMetaData(SeasonAttacher::class));
    },
    LeagueRepository::class => function (ContainerInterface $container): LeagueRepository {
        $entityManager = $container->get(\Doctrine\ORM\EntityManager::class);
        return new LeagueRepository($entityManager, $entityManager->getClassMetaData(League::class));
    },
    LeagueAttacherRepository::class => function (ContainerInterface $container): LeagueAttacherRepository {
        $entityManager = $container->get(\Doctrine\ORM\EntityManager::class);
        return new LeagueAttacherRepository($entityManager, $entityManager->getClassMetaData(LeagueAttacher::class));
    },
    CompetitionRepository::class => function (ContainerInterface $container): CompetitionRepository {
        $entityManager = $container->get(\Doctrine\ORM\EntityManager::class);
        return new CompetitionRepository($entityManager, $entityManager->getClassMetaData(Competition::class));
    },
    CompetitionAttacherRepository::class => function (ContainerInterface $container): CompetitionAttacherRepository {
        $entityManager = $container->get(\Doctrine\ORM\EntityManager::class);
        return new CompetitionAttacherRepository($entityManager, $entityManager->getClassMetaData(CompetitionAttacher::class));
    },
    CompetitorRepository::class => function (ContainerInterface $container): CompetitorRepository {
        $entityManager = $container->get(\Doctrine\ORM\EntityManager::class);
        return new CompetitorRepository($entityManager, $entityManager->getClassMetaData(Competitor::class));
    },
    CompetitorAttacherRepository::class => function (ContainerInterface $container): CompetitorAttacherRepository {
        $entityManager = $container->get(\Doctrine\ORM\EntityManager::class);
        return new CompetitorAttacherRepository($entityManager, $entityManager->getClassMetaData(CompetitorAttacher::class));
    },
    ExternalSourceRepository::class => function (ContainerInterface $container): ExternalSourceRepository {
        $entityManager = $container->get(\Doctrine\ORM\EntityManager::class);
        return new ExternalSourceRepository($entityManager, $entityManager->getClassMetaData(ExternalSource::class));
    },
    CacheItemDbRepository::class => function (ContainerInterface $container): CacheItemDbRepository {
        $entityManager = $container->get(\Doctrine\ORM\EntityManager::class);
        return new CacheItemDbRepository($entityManager, $entityManager->getClassMetaData(CacheItemDb::class));
    },

    GameRepository::class => function (ContainerInterface $container): GameRepository {
        $entityManager = $container->get(\Doctrine\ORM\EntityManager::class);
        return new GameRepository($entityManager, $entityManager->getClassMetaData(Game::class));
    },
    GameAttacherRepository::class => function (ContainerInterface $container): GameAttacherRepository {
        $entityManager = $container->get(\Doctrine\ORM\EntityManager::class);
        return new GameAttacherRepository($entityManager, $entityManager->getClassMetaData(GameAttacher::class));
    },
    GameScoreRepository::class => function (ContainerInterface $container): GameScoreRepository {
        $entityManager = $container->get(\Doctrine\ORM\EntityManager::class);
        return new GameScoreRepository($entityManager, $entityManager->getClassMetaData(GameScore::class));
    }
];
