<?php

declare(strict_types=1);

namespace Qubus\Tests\Validation\Rules;

use PHPUnit\Framework\Assert;
use Qubus\Validation\Rules\Alpha;
use PHPUnit\Framework\TestCase;
use stdClass;

class AlphaTest extends TestCase
{

    public function setUp(): void
    {
        $this->rule = new Alpha;
    }

    public function testValids()
    {
        Assert::assertTrue($this->rule->check('foo'));
        Assert::assertTrue($this->rule->check('foobar'));
    }

    public function testInvalids()
    {
        Assert::assertFalse($this->rule->check(2));
        Assert::assertFalse($this->rule->check([]));
        Assert::assertFalse($this->rule->check(new stdClass));
        Assert::assertFalse($this->rule->check('123asd'));
        Assert::assertFalse($this->rule->check('asd123'));
        Assert::assertFalse($this->rule->check('foo123bar'));
        Assert::assertFalse($this->rule->check('foo bar'));
    }
}
