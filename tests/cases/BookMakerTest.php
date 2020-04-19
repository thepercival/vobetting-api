<?php
/**
 * Created by PhpStorm.
 * User: coen
 * Date: 8-6-19
 * Time: 21:27
 */

namespace Voetbal\Tests;

use VOBetting\Bookmaker;

class BookMakerTest extends \PHPUnit\Framework\TestCase
{
    public function testWinnersOrLosersDescription()
    {
        $name = "MyBookie";
        $bookmaker = new Bookmaker($name, false );

        $this->assertSame($bookmaker->getName(), $name);
        $this->assertSame($bookmaker->getExchange(), false);
    }
}
