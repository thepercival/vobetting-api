<?php

namespace VOBetting;

use Sports\Competitor\Base;

class CompetitorDep
{
    /**
     * @var int|string
     */
    protected $id;

    protected $registeredDep;
    protected $infoDep;
    protected $nameDep;
    protected $abbreviationDep;
    protected $imageUrlDep;
    protected $associationDep;

    use Base;

    public function __construct()
    {

    }
}
