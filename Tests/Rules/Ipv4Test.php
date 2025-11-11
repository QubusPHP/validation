<?php

declare(strict_types=1);

namespace Qubus\Tests\Validation\Rules;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Qubus\Validation\Rules\Ipv4;

class Ipv4Test extends TestCase
{

    public function setUp(): void
    {
        $this->rule = new Ipv4;
    }

    public function testValids()
    {
        Assert::assertTrue($this->rule->check('0.0.0.0'));
        Assert::assertTrue($this->rule->check('1.2.3.4'));
        Assert::assertTrue($this->rule->check('255.255.255.255'));
    }

    public function testInvalids()
    {
        Assert::assertFalse($this->rule->check('hf02::2'));
        Assert::assertFalse($this->rule->check('12345:0000:3238:DFE1:0063:0000:0000:FEFB'));
    }
}
