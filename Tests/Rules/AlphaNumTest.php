<?php

declare(strict_types=1);

namespace Qubus\Tests\Validation\Rules;

use PHPUnit\Framework\Assert;
use Qubus\Validation\Rules\AlphaNum;
use PHPUnit\Framework\TestCase;

class AlphaNumTest extends TestCase
{

    public function setUp(): void
    {
        $this->rule = new AlphaNum;
    }

    public function testValids()
    {
        Assert::assertTrue($this->rule->check('123'));
        Assert::assertTrue($this->rule->check('abc'));
        Assert::assertTrue($this->rule->check('123abc'));
        Assert::assertTrue($this->rule->check('abc123'));
    }

    public function testInvalids()
    {
        Assert::assertFalse($this->rule->check('foo 123'));
        Assert::assertFalse($this->rule->check('123 foo'));
        Assert::assertFalse($this->rule->check(' foo123 '));
    }
}
