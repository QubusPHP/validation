<?php

declare(strict_types=1);

namespace Qubus\Tests\Validation\Rules;

use PHPUnit\Framework\Assert;
use Qubus\Validation\Rules\Accepted;
use PHPUnit\Framework\TestCase;

class AcceptedTest extends TestCase
{
    public function setUp(): void
    {
        $this->rule = new Accepted();
    }

    public function testValids()
    {
        Assert::assertTrue($this->rule->check('yes'));
        Assert::assertTrue($this->rule->check('on'));
        Assert::assertTrue($this->rule->check('1'));
        Assert::assertTrue($this->rule->check(1));
        Assert::assertTrue($this->rule->check(true));
        Assert::assertTrue($this->rule->check('true'));
    }

    public function testInvalids()
    {
        Assert::assertFalse($this->rule->check(''));
        Assert::assertFalse($this->rule->check('onn'));
        Assert::assertFalse($this->rule->check(' 1'));
        Assert::assertFalse($this->rule->check(10));
    }
}
