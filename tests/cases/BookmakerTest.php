<?php
/**
 * Created by PhpStorm.
 * User: coen
 * Date: 8-6-19
 * Time: 21:27
 */

namespace VOBetting\Tests;

use VOBetting\Bookmaker;

class BookmakerTest extends \PHPUnit\Framework\TestCase
{
    public function testWinnersOrLosersDescription()
    {
        $name = "MyBookie";
        $bookmaker = new Bookmaker($name, false);

        self::assertSame($bookmaker->getName(), $name);
        self::assertSame($bookmaker->getExchange(), false);
    }
}
