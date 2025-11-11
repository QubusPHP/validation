<?php

declare(strict_types=1);

namespace Qubus\Tests\Validation\Rules;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Qubus\Validation\Rules\TypeString;
use stdClass;

class TypeStringTest extends TestCase
{
    public function setUp(): void
    {
        $this->rule = new TypeString();
    }

    public function testValids()
    {
        Assert::assertTrue($this->rule->check('foo'));
        Assert::assertTrue($this->rule->check('123asd'));
        Assert::assertTrue($this->rule->check('asd123'));
        Assert::assertTrue($this->rule->check('foo123bar'));
        Assert::assertTrue($this->rule->check('foo bar'));
        Assert::assertTrue(
            $this->rule->check('<p><a href="#">Lorem ipsum dolor sit amet</a> cum omnis voluptatum! </p>')
        );
    }

    public function testInvalids()
    {
        Assert::assertFalse($this->rule->check(2));
        Assert::assertFalse($this->rule->check([]));
        Assert::assertFalse($this->rule->check(new stdClass));
    }
}
