<?php

namespace PeterColes\Betfair;

use PeterColes\Betfair\Api\Auth;

class Betfair
{
    public function betting($params)
    {
        return $this->action("betting", $params);
    }

    protected function action($method, $params)
    {
        // alias for Auth's init and persist methods
        if ($method == 'init' || $method == 'persist') {
            return call_user_func_array([ new Auth, $method ], $params);
        }

        // standard Auth
        if ($method == 'auth') {
            return new Auth;
        }

        // all other subsystems, currently Betting and Account
        $class = 'PeterColes\\Betfair\\Api\\'.ucfirst($method);
        if (class_exists($class)) {
            return call_user_func([ new $class, 'execute' ], $params);
        }
    }
}
