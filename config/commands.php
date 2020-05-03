<?php

declare(strict_types=1);

use Psr\Container\ContainerInterface;

use App\Commands\Import as ImportCommand;
use App\Commands\GetExternal as GetExternalCommand;

return [
    "app:import" => function (ContainerInterface $container): ImportCommand {
        return new ImportCommand($container);
    },
    "app:getexternal" => function (ContainerInterface $container): GetExternalCommand {
        return new GetExternalCommand($container);
    }
];
