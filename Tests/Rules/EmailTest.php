<?php

declare(strict_types=1);

namespace Qubus\Tests\Validation\Rules;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Qubus\Validation\Rules\Email;

class EmailTest extends TestCase
{
    public function setUp(): void
    {
        $this->rule = new Email();
    }

    public function testValids()
    {
        Assert::assertTrue($this->rule->check('johndoe@gmail.com'));
        Assert::assertTrue($this->rule->check('johndoe@foo.bar'));
        Assert::assertTrue($this->rule->check('foo123123@foo.bar.baz'));
    }

    public function testInvalids()
    {
        Assert::assertFalse($this->rule->check(1));
        Assert::assertFalse($this->rule->check('john doe@gmail.com'));
        Assert::assertFalse($this->rule->check('johndoe'));
        Assert::assertFalse($this->rule->check('johndoe.gmail.com'));
        Assert::assertFalse($this->rule->check('johndoe.gmail.com'));
    }
}
