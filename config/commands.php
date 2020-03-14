<?php

declare(strict_types=1);

use Psr\Container\ContainerInterface;

use App\Commands\Import\Voetbal as VoetbalImportCommand;
use App\Commands\Attach\Voetbal as VoetbalAttachCommand;

return [
    "app:import-voetbal" => function (ContainerInterface $container) {
        return new VoetbalImportCommand($container);
    },
    "app:attach-voetbal" => function (ContainerInterface $container) {
        return new VoetbalAttachCommand($container);
    },
];