<?php

declare(strict_types=1);

namespace Qubus\Tests\Validation\Rules;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Qubus\Validation\Rules\DigitsBetween;

class DigitsBetweenTest extends TestCase
{

    public function setUp(): void
    {
        $this->rule = new DigitsBetween;
    }

    public function testValids()
    {
        Assert::assertTrue($this->rule->fillParameters([2, 6])->check(12345));
        Assert::assertTrue($this->rule->fillParameters([2, 3])->check(12));
        Assert::assertTrue($this->rule->fillParameters([2, 3])->check(123));
        Assert::assertTrue($this->rule->fillParameters([3, 5])->check('12345'));
    }

    public function testInvalids()
    {
        Assert::assertFalse($this->rule->fillParameters([4, 6])->check(12));
        Assert::assertFalse($this->rule->fillParameters([1, 3])->check(12345));
        Assert::assertFalse($this->rule->fillParameters([1, 3])->check(12345));
        Assert::assertFalse($this->rule->fillParameters([3, 6])->check('foobar'));
    }
}
