<?php

declare(strict_types=1);

namespace Qubus\Tests\Validation\Rules;

use PHPUnit\Framework\Assert;
use Qubus\Validation\Rules\AlphaSpaces;
use PHPUnit\Framework\TestCase;

class AlphaSpacesTest extends TestCase
{

    public function setUp(): void
    {
        $this->rule = new AlphaSpaces;
    }

    public function testValids()
    {
        Assert::assertTrue($this->rule->check('abc'));
        Assert::assertTrue($this->rule->check('foo bar'));
        Assert::assertTrue($this->rule->check('foo bar  bar'));
    }

    public function testInvalids()
    {
        Assert::assertFalse($this->rule->check('123'));
        Assert::assertFalse($this->rule->check('123abc'));
        Assert::assertFalse($this->rule->check('abc123'));
        Assert::assertFalse($this->rule->check('foo_123'));
        Assert::assertFalse($this->rule->check('213-foo'));
    }
}
