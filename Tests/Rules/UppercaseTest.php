<?php

declare(strict_types=1);

namespace Qubus\Tests\Validation\Rules;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Qubus\Validation\Rules\Uppercase;

class UppercaseTest extends TestCase
{
    public function setUp(): void
    {
        $this->rule = new Uppercase;
    }

    public function testValids()
    {
        Assert::assertTrue($this->rule->check('USERNAME'));
        Assert::assertTrue($this->rule->check('FULL NAME'));
        Assert::assertTrue($this->rule->check('FULL_NAME'));
    }

    public function testInvalids()
    {
        Assert::assertFalse($this->rule->check('username'));
        Assert::assertFalse($this->rule->check('Username'));
        Assert::assertFalse($this->rule->check('userName'));
        Assert::assertFalse($this->rule->check([]));
    }
}
