<?php

declare(strict_types=1);

namespace Qubus\Tests\Validation\Rules;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Qubus\Validation\Rules\In;

class InTest extends TestCase
{

    public function setUp(): void
    {
        $this->rule = new In;
    }

    public function testValids()
    {
        Assert::assertTrue($this->rule->fillParameters([1,2,3])->check(1));
        Assert::assertTrue($this->rule->fillParameters(['1', 'bar', '3'])->check('bar'));
    }

    public function testInvalids()
    {
        Assert::assertFalse($this->rule->fillParameters([1,2,3])->check(4));
    }

    public function testStricts()
    {
        // Not strict
        Assert::assertTrue($this->rule->fillParameters(['1', '2', '3'])->check(1));
        Assert::assertTrue($this->rule->fillParameters(['1', '2', '3'])->check(true));

        // Strict
        $this->rule->strict();
        Assert::assertFalse($this->rule->fillParameters(['1', '2', '3'])->check(1));
        Assert::assertFalse($this->rule->fillParameters(['1', '2', '3'])->check(1));
    }
}
