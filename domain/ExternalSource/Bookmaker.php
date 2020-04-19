<?php

namespace VOBetting\ExternalSource;

use VOBetting\Bookmaker as BookmakerBase;

interface Bookmaker
{
    /**
     * @return array|BookmakerBase[]
     */
    public function getBookmakers(): array;
    /**
     * @param mixed $id
     * @return BookmakerBase|null
     */
    public function getBookmaker($id): ?BookmakerBase;
}
