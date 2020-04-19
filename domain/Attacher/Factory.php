<?php

namespace VOBetting\Attacher;

use Voetbal\ExternalSource;
use Voetbal\Import\Idable as Importable;
use Voetbal\Attacher as AttacherBase;
use Voetbal\Attacher\Factory as VoetbalAttacherFactory;
use VOBetting\Attacher\Bookmaker as BookmakerAttacher;
use VOBetting\Bookmaker;

class Factory extends VoetbalAttacherFactory
{
    public function createObject(Importable $importable, ExternalSource $externalSource, $externalId): ?AttacherBase
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
