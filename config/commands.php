<?php

declare(strict_types=1);

use Psr\Container\ContainerInterface;

use App\Commands\Planning\CreateDefaultInput as PlanningCreateDefaultInput;
use App\Commands\Planning\Create as PlanningCreate;
use App\Commands\Planning\RetryTimeout as PlanningRetryTimeout;
use App\Commands\UpdateSitemap;
use App\Commands\BackupSponsorImages;

return [
    /*"app:create-default-planning-input" => function (ContainerInterface $container) {
        return new PlanningCreateDefaultInput($container);
    },
    "app:create-planning" => function (ContainerInterface $container) {
        return new PlanningCreate($container);
    },
    "app:retry-timeout-planning" => function (ContainerInterface $container) {
        return new PlanningRetryTimeout($container);
    },
    "app:update-sitemap" => function (ContainerInterface $container) {
        return new UpdateSitemap($container);
    },
    "app:backup-sponsorimages" => function (ContainerInterface $container) {
        return new BackupSponsorImages($container);
    }*/
];