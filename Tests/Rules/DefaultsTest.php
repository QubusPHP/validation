<?php

declare(strict_types=1);

namespace Qubus\Tests\Validation\Rules;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Qubus\Validation\Rules\Defaults;

class DefaultsTest extends TestCase
{
    public function setUp(): void
    {
        $this->rule = new Defaults;
    }

    public function testDefaults()
    {
        Assert::assertTrue($this->rule->fillParameters([10])->check(0));
        Assert::assertTrue($this->rule->fillParameters(['something'])->check(null));
        Assert::assertTrue($this->rule->fillParameters([[1,2,3]])->check(false));
        Assert::assertTrue($this->rule->fillParameters([[1,2,3]])->check([]));
    }
}
