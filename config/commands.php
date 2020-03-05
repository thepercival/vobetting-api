<?php

declare(strict_types=1);

use Psr\Container\ContainerInterface;

use App\Commands\Import\Voetbal as VoetbalImportCommand;

return [
    "app:import-voetbal" => function (ContainerInterface $container) {
        return new VoetbalImportCommand($container);
    }
];