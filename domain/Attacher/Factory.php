<?php

namespace VOBetting\Attacher;

use SportsImport\ExternalSource;
use SportsHelpers\Identifiable;
use SportsImport\Attacher as AttacherBase;
use SportsImport\Attacher\Factory as AttacherFactory;
use VOBetting\Attacher\Bookmaker as BookmakerAttacher;
use VOBetting\Bookmaker;

class Factory extends AttacherFactory
{
    public function createObject(Identifiable $importable, ExternalSource $externalSource, $externalId): ?AttacherBase
    {
        if ($importable instanceof Bookmaker) {
            return new BookmakerAttacher(
                $importable,
                $externalSource,
                $externalId
            );
        }
        return parent::createObject($importable, $externalSource, $externalId);
    }
}
