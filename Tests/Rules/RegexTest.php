<?php

declare(strict_types=1);

namespace Qubus\Tests\Validation\Rules;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Qubus\Validation\Rules\Regex;

class RegexTest extends TestCase
{
    public function setUp(): void
    {
        $this->rule = new Regex;
    }

    public function testValids()
    {
        Assert::assertTrue($this->rule->fillParameters(["/^F/i"])->check("foo"));
    }

    public function testInvalids()
    {
        Assert::assertFalse($this->rule->fillParameters(["/^F/i"])->check("bar"));
        Assert::assertFalse($this->rule->fillParameters(["invalid"])->check("bar"));
        Assert::assertFalse($this->rule->fillParameters(["/^F/i"])->check([]));
    }
}
