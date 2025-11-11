<?php

declare(strict_types=1);

namespace Qubus\Tests\Validation\Rules;

use PHPUnit\Framework\Assert;
use Qubus\Validation\Rules\AlphaDash;
use PHPUnit\Framework\TestCase;

class AlphaDashTest extends TestCase
{

    public function setUp(): void
    {
        $this->rule = new AlphaDash;
    }

    public function testValids()
    {
        Assert::assertTrue($this->rule->check('123'));
        Assert::assertTrue($this->rule->check('abc'));
        Assert::assertTrue($this->rule->check('123abc'));
        Assert::assertTrue($this->rule->check('abc123'));
        Assert::assertTrue($this->rule->check('foo_123'));
        Assert::assertTrue($this->rule->check('213-foo'));
    }

    public function testInvalids()
    {
        Assert::assertFalse($this->rule->check('foo bar'));
        Assert::assertFalse($this->rule->check('123 bar '));
    }
}
