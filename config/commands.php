<?php

declare(strict_types=1);

use Psr\Container\ContainerInterface;

use App\Commands\Import as ImportCommand;

return [
    "app:import" => function (ContainerInterface $container): ImportCommand {
        return new ImportCommand($container);
    }
];
