<?php

declare(strict_types=1);

namespace Qubus\Tests\Validation\Rules;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Qubus\Validation\Rules\Lowercase;

class LowercaseTest extends TestCase
{

    public function setUp(): void
    {
        $this->rule = new Lowercase;
    }

    public function testValids()
    {
        Assert::assertTrue($this->rule->check('username'));
        Assert::assertTrue($this->rule->check('full name'));
        Assert::assertTrue($this->rule->check('full_name'));
    }

    public function testInvalids()
    {
        Assert::assertFalse($this->rule->check('USERNAME'));
        Assert::assertFalse($this->rule->check('Username'));
        Assert::assertFalse($this->rule->check('userName'));
        Assert::assertFalse($this->rule->check([]));
    }
}
