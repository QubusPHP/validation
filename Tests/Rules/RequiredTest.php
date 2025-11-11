<?php

declare(strict_types=1);

namespace Qubus\Tests\Validation\Rules;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Qubus\Validation\Rules\Required;
use stdClass;

class RequiredTest extends TestCase
{
    public function setUp(): void
    {
        $this->rule = new Required();
    }

    public function testValids()
    {
        Assert::assertTrue($this->rule->check('foo'));
        Assert::assertTrue($this->rule->check([1]));
        Assert::assertTrue($this->rule->check(1));
        Assert::assertTrue($this->rule->check(true));
        Assert::assertTrue($this->rule->check('0'));
        Assert::assertTrue($this->rule->check(0));
        Assert::assertTrue($this->rule->check(new stdClass()));
    }

    public function testInvalids()
    {
        Assert::assertFalse($this->rule->check(null));
        Assert::assertFalse($this->rule->check(''));
        Assert::assertFalse($this->rule->check([]));
    }
}
