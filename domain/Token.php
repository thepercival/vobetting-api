<?php

declare(strict_types=1);

namespace VOBetting;

class Token
{
    protected $decoded;

    public function __construct(array $decoded)
    {
        $this->populate($decoded);
    }

    public function populate($decoded)
    {
        $this->decoded = $decoded;
    }

    public function hasScope(array $scope)
    {
        $arr = array_intersect($scope, $this->decoded["scope"]);
        return count($arr) > 0;
    }

    public function isPopulated()
    {
        return $this->decoded !== null;
    }

    public function getUserId()
    {
        return $this->decoded["sub"];
    }
}
