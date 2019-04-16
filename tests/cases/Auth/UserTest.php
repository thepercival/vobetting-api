<?php
/**
 * Created by PhpStorm.
 * User: coen
 * Date: 28-1-17
 * Time: 21:24
 */

namespace VOBettingTest\Auth;

use \VOBetting\User;

class UserTest extends \PHPUnit\Framework\TestCase
{
	public function testEmailByConstructorMin()
	{
		$this->expectException(\InvalidArgumentException::class);
		$user = new User("12");
	}

	public function testEmailByConstructorValid()
	{
		$this->expectException(\InvalidArgumentException::class);
		$user = new User("1234567890123456");
	}
}