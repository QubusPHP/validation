<?php

declare(strict_types=1);

namespace Qubus\Tests\Validation\Rules;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Qubus\Validation\Rules\Digits;

class DigitsTest extends TestCase
{

    public function setUp(): void
    {
        $this->rule = new Digits;
    }

    public function testValids()
    {
        Assert::assertTrue($this->rule->fillParameters([4])->check(1243));
        Assert::assertTrue($this->rule->fillParameters([6])->check(124567));
        Assert::assertTrue($this->rule->fillParameters([3])->check('123'));
    }

    public function testInvalids()
    {
        Assert::assertFalse($this->rule->fillParameters([7])->check(12345678));
        Assert::assertFalse($this->rule->fillParameters([4])->check(12));
        Assert::assertFalse($this->rule->fillParameters([3])->check('foo'));
    }
}
