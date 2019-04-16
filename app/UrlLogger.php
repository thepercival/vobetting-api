<?php
/**
 * Created by PhpStorm.
 * User: coen
 * Date: 12-4-19
 * Time: 19:16
 */

namespace App;

use Monolog\Logger;

class UrlLogger extends Logger
{
    /**
     * @var string
     */
    private $url;

    public function __construct(string $name, string $url )
    {
        $this->url = $url;
        parent::__construct( $name );
    }

    public function addNotice(string $action, $message, array $context = array())
    {
        $this->addNotice($message, $context );
        return $this->addNotice( 'link voor regel hierboven ' . $this->url . $action, $context );
    }

    public function addError( $message, array $context = array())
    {
        return $this->addError($message, $context );
    }
}