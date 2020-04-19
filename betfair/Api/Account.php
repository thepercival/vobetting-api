<?php

namespace PeterColes\Betfair\Api;

use PeterColes\Betfair\Api\BaseApi;

class Account extends BaseApi
{
    /**
     * Betfair API endpoint for account subsystem requests
     */
    private const ENDPOINT = 'https://api.betfair.com/exchange/account/rest/v1.0/';

    public function __construct()
    {
        parent::__construct(Account::ENDPOINT);
    }
}
