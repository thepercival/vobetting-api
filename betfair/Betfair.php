<?php

namespace PeterColes\Betfair;

use PeterColes\Betfair\Api\Auth;

class Betfair
{
    public function betting($params)
    {
        return $this->action("betting", $params);
    }

    public function login( $appKey, $username, $password )
    {
        $auth = new Auth();
        $auth->init( $appKey, $username, $password );
    }

    protected function action($method, $params)
    {
        // all other subsystems, currently Betting and Account
        $class = 'PeterColes\\Betfair\\Api\\'.ucfirst($method);
        if (class_exists($class)) {
            return call_user_func([ new $class, 'execute' ], $params);
        }
    }
}
