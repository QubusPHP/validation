<?php

declare(strict_types=1);

namespace Qubus\Tests\Validation\Rules;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Qubus\Validation\Rules\Callback;

class CallbackTest extends TestCase
{

    public function setUp(): void
    {
        $this->rule = new Callback;
        $this->rule->setCallback(function ($value) {
            return (is_numeric($value) and $value % 2 === 0);
        });
    }

    public function testValids()
    {
        Assert::assertTrue($this->rule->check(2));
        Assert::assertTrue($this->rule->check('4'));
        Assert::assertTrue($this->rule->check("1000"));
    }

    public function testInvalids()
    {
        Assert::assertFalse($this->rule->check(1));
        Assert::assertFalse($this->rule->check('abc12'));
        Assert::assertFalse($this->rule->check("12abc"));
    }
}
