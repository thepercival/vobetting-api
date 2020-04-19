<?php
/**
 * Created by PhpStorm.
 * User: coen
 * Date: 12-4-18
 * Time: 10:27
 */

namespace VOBetting;

class Bookmaker
{
    /**
     * @var int
     */
    private $id;
    /**
     * @var string
     */
    private $name;
    /**
     * @var boolean
     */
    private $exchange;

    const MIN_LENGTH_NAME = 2;
    const MAX_LENGTH_NAME = 15;

    public function __construct(string $name, bool $exchange)
    {
        $this->setName($name);
        $this->setExchange($exchange);
    }

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        if (strlen($name) < static::MIN_LENGTH_NAME or strlen($name) > static::MAX_LENGTH_NAME) {
            throw new \InvalidArgumentException("de naam moet minimaal ".static::MIN_LENGTH_NAME." karakters bevatten en mag maximaal ".static::MAX_LENGTH_NAME." karakters bevatten", E_ERROR);
        }
        $this->name = $name;
    }

    /**
     * Get exchange
     *
     * @return boolean
     */
    public function getExchange()
    {
        return $this->exchange;
    }

    /**
     * @param bool $exchange
     */
    public function setExchange($exchange)
    {
        $this->exchange = $exchange;
    }
}
