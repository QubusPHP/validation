<?php

declare(strict_types=1);

namespace Qubus\Tests\Validation\Rules;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Qubus\Validation\Rules\Ipv6;

class Ipv6Test extends TestCase
{

    public function setUp(): void
    {
        $this->rule = new Ipv6;
    }

    public function testValids()
    {
        Assert::assertTrue($this->rule->check('2001:0000:3238:DFE1:0063:0000:0000:FEFB'));
        Assert::assertTrue($this->rule->check('ff02::2'));
    }

    public function testInvalids()
    {
        Assert::assertFalse($this->rule->check('hf02::2'));
        Assert::assertFalse($this->rule->check('12345:0000:3238:DFE1:0063:0000:0000:FEFB'));
    }
}
