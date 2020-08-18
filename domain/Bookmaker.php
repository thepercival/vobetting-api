<?php
/**
 * Created by PhpStorm.
 * User: coen
 * Date: 12-4-18
 * Time: 10:27
 */

namespace VOBetting;

use SportsHelpers\Identifiable;

class Bookmaker implements Identifiable
{
    /**
     * @var int|string
     */
    protected $id;
    /**
     * @var string
     */
    protected $name;
    /**
     * @var bool
     */
    protected $exchange;
    /**
     * @var float
     */
    protected $feePercentage = 0;

    const MIN_LENGTH_NAME = 2;
    const MAX_LENGTH_NAME = 15;

    public function __construct(string $name, bool $exchange)
    {
        $this->setName($name);
        $this->setExchange($exchange);
    }

    /**
     * @return int|string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int|string $id
     * @return void
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        if (strlen($name) < static::MIN_LENGTH_NAME or strlen($name) > static::MAX_LENGTH_NAME) {
            throw new \InvalidArgumentException("de naam moet minimaal ".static::MIN_LENGTH_NAME." karakters bevatten en mag maximaal ".static::MAX_LENGTH_NAME." karakters bevatten", E_ERROR);
        }
        $this->name = $name;
    }

    /**
     * Get exchange
     *
     * @return bool
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

    public function getFeePercentage(): float
    {
        if( $this->feePercentage == null ) {
            return 0.0;
        }
        return $this->feePercentage;
    }

    public function setFeePercentage(float $feePercentage)
    {
        $this->feePercentage = $feePercentage;
    }
}
