<?php

declare(strict_types=1);

namespace Qubus\Tests\Validation\Rules;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Qubus\Validation\Rules\TypeArray;

class TypeArrayTest extends TestCase
{

    public function setUp(): void
    {
        $this->rule = new TypeArray;
    }

    public function testValids()
    {
        Assert::assertTrue($this->rule->check([]));
        Assert::assertTrue($this->rule->check([1,2,3]));
        Assert::assertTrue($this->rule->check([1,2,[4,5,6]]));
    }

    public function testInvalids()
    {
        Assert::assertFalse($this->rule->check('[]'));
        Assert::assertFalse($this->rule->check('[1,2,3]'));
    }
}
