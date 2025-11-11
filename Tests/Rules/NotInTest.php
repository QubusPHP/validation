<?php

declare(strict_types=1);

namespace Qubus\Tests\Validation\Rules;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Qubus\Validation\Rules\NotIn;

class NotInTest extends TestCase
{
    public function setUp(): void
    {
        $this->rule = new NotIn;
    }

    public function testValids()
    {
        Assert::assertTrue($this->rule->fillParameters(['2', '3', '4'])->check('1'));
        Assert::assertTrue($this->rule->fillParameters([1, 2, 3])->check(5));
    }

    public function testInvalids()
    {
        Assert::assertFalse($this->rule->fillParameters(['bar', 'baz', 'qux'])->check('bar'));
    }

    public function testStricts()
    {
        // Not strict
        Assert::assertFalse($this->rule->fillParameters(['1', '2', '3'])->check(1));
        Assert::assertFalse($this->rule->fillParameters(['1', '2', '3'])->check(true));

        // Strict
        $this->rule->strict();
        Assert::assertTrue($this->rule->fillParameters(['1', '2', '3'])->check(1));
        Assert::assertTrue($this->rule->fillParameters(['1', '2', '3'])->check(1));
    }
}
