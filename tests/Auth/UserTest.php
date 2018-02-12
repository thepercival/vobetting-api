<?php
/**
 * Created by PhpStorm.
 * User: coen
 * Date: 28-1-17
 * Time: 21:24
 */

namespace VOBetting\Tests\Auth;

use \VOBetting\Auth\User as User;

class UserTest extends \PHPUnit_Framework_TestCase
{
	public function testCreateNameMin()
	{
		$this->expectException(\InvalidArgumentException::class);
		$name = new User\Name("12");
	}

	public function testCreateNameMax()
	{
		$this->expectException(\InvalidArgumentException::class);
		$name = new User\Name("1234567890123456");
	}

	public function testCreateNameAlphaN()
	{
		$this->expectException(\InvalidArgumentException::class);
		$name = new User\Name("12AA.");
	}
}