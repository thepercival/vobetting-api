<?php

namespace VOBetting\ExternalSource;

use VOBetting\LayBack as LayBackBase;
use Sports\Competition;

interface LayBack
{
    /**
     * @param Competition $competition
     * @return array|LayBackBase[]
     */
    public function getLayBacks(Competition $competition): array;
    /**
     * @param Competition $competition
     * @param mixed $id
     * @return LayBackBase|null
     */
    // public function getLayBack(Competition $competition, $id): ?LayBackBase;
}
