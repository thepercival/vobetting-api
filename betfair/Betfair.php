<?php

namespace PeterColes\Betfair;

class Betfair
{
    /**
     * @var Api\Auth
     */
    private $auth;

    public function __construct(string $appKey, string $username, string $password)
    {
        $this->auth = new Api\Auth($appKey, $username, $password);
    }

    public function betting($params)
    {
        $this->auth->init();
        $betting = new Api\Betting();
        return $betting->executeWithParams($params, $this->auth->getHeaders());
    }
}
