<?php

declare(strict_types=1);

namespace Qubus\Tests\Validation\Rules;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Qubus\Tests\Validation\Fixtures\UserRole;
use Qubus\Tests\Validation\Fixtures\UserStatus;
use Qubus\Validation\Rules\Enum;

class EnumTest extends TestCase
{
    public function setUp(): void
    {
        $this->rule = new Enum();
    }

    public function testValids()
    {
        Assert::assertTrue($this->rule->check(value: UserRole::class));
        Assert::assertTrue($this->rule->check(value: UserRole::tryFrom('admin')));
        Assert::assertTrue($this->rule->check(value: UserRole::MANAGER));
    }

    public function testInvalids()
    {
        Assert::assertFalse($this->rule->check(value: UserStatus::class));
    }
}
