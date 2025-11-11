<?php

declare(strict_types=1);

namespace Qubus\Tests\Validation\Rules;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Qubus\Validation\Rules\Json;

class JsonTest extends TestCase
{

    public function setUp(): void
    {
        $this->rule = new Json;
    }

    public function testValids()
    {
        Assert::assertTrue($this->rule->check('{}'));
        Assert::assertTrue($this->rule->check('[]'));
        Assert::assertTrue($this->rule->check('false'));
        Assert::assertTrue($this->rule->check('null'));
        Assert::assertTrue($this->rule->check('{"username": "John Doe"}'));
        Assert::assertTrue($this->rule->check('{"number": 12345678}'));
    }

    public function testInvalids()
    {
        Assert::assertFalse($this->rule->check(''));
        Assert::assertFalse($this->rule->check(123));
        Assert::assertFalse($this->rule->check(false));
        Assert::assertFalse($this->rule->check('{"username": John Doe}'));
        Assert::assertFalse($this->rule->check('{number: 12345678}'));
    }
}
