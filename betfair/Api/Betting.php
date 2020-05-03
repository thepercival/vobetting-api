<?php

namespace PeterColes\Betfair\Api;

use PeterColes\Betfair\Api\BaseApi;

class Betting extends BaseApi
{
    private const ENDPOINT = 'https://api.betfair.com/exchange/betting/rest/v1.0/';

    public function __construct()
    {
        parent::__construct(Betting::ENDPOINT);
    }

    /**
     * Prepare parameters for API requests, ensuring the mandatory requirments are satisfied
     */
    public function prepare(array $params)
    {
        $this->params = count($params) > 0 ? $params[ 0 ] : [ ];

        // force mandatory fields
        $this->filter();
        $this->maxRecords();
    }

    /**
     * Ensure that a filter parameter is passed where mandatory
     */
    protected function filter()
    {
        $lists = [
            'listCompetitions',
            'listCountries',
            'listEvents',
            'listEventTypes',
            'listMarketTypes',
            'listVenues',
            'listMarketCatalogue'
        ];

        if (in_array($this->method, $lists, true) && array_key_exists('filter', $this->params)) {
            $this->params[ 'filter' ] = new \stdClass;
        }
    }

    /**
     * Ensure that a maxRecord parameter is passed where mandatory
     */
    protected function maxRecords()
    {
        if ($this->method == 'listMarketCatalogue' && array_key_exists('maxResults', $this->params) ) {
            $this->params[ 'maxResults' ] = 1000;
        }
    }
}
