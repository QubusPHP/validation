<?php

declare(strict_types=1);

namespace Qubus\Tests\Validation\Rules;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Qubus\Validation\Rules\Integer;

class IntegerTest extends TestCase
{

    public function setUp(): void
    {
        $this->rule = new Integer;
    }

    public function testValids()
    {
        Assert::assertTrue($this->rule->check(0));
        Assert::assertTrue($this->rule->check('0'));
        Assert::assertTrue($this->rule->check('123'));
        Assert::assertTrue($this->rule->check('-123'));
        Assert::assertTrue($this->rule->check(123));
        Assert::assertTrue($this->rule->check(-123));
    }

    public function testInvalids()
    {
        Assert::assertFalse($this->rule->check('foo123'));
        Assert::assertFalse($this->rule->check('123foo'));
        Assert::assertFalse($this->rule->check([123]));
        Assert::assertFalse($this->rule->check('123.456'));
        Assert::assertFalse($this->rule->check('-123.456'));
    }
}
