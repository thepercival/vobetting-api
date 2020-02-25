<?php


namespace FCToernooi\Tournament;

use Voetbal\Range as VoetbalRange;
use Voetbal\Structure\Options as VoetbalStructureOptions;

class StructureOptions extends VoetbalStructureOptions
{
    public function __construct()
    {
        parent::__construct(
            new VoetbalRange(1, 16),
            new VoetbalRange( 2, 40),
            new VoetbalRange( 2, 12)
        );
    }
}