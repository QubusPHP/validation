<?php

declare(strict_types=1);

namespace Qubus\Tests\Validation\Rules;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Qubus\Validation\Rules\Date;

class DateTest extends TestCase
{

    public function setUp(): void
    {
        $this->rule = new Date;
    }

    public function testValids()
    {
        Assert::assertTrue($this->rule->check("2010-10-10"));
        Assert::assertTrue($this->rule->fillParameters(['d-m-Y'])->check("10-10-2010"));
    }

    public function testInvalids()
    {
        Assert::assertFalse($this->rule->check("10-10-2010"));
        Assert::assertFalse($this->rule->fillParameters(['Y-m-d'])->check("2010-10-10 10:10"));
        Assert::assertFalse($this->rule->check("2024-02-30"));
        Assert::assertFalse($this->rule->check([]));
    }
}
