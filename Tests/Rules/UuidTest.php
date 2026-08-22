<?php

declare(strict_types=1);

namespace Qubus\Tests\Validation\Rules;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Qubus\Validation\Rules\Uuid;

class UuidTest extends TestCase
{
    protected function setUp(): void
    {
        $this->rule = new Uuid();
    }

    public function testValids()
    {
        Assert::assertTrue($this->rule->check('86e51afc-c626-4b28-999f-560a297d019f'));
        Assert::assertTrue($this->rule->check('ebc87949-192c-48ca-9822-d7a173193eaf'));
    }

    public function testInvalids()
    {
        Assert::assertFalse($this->rule->check('uuid'));
        Assert::assertFalse($this->rule->check('null'));
        Assert::assertFalse($this->rule->check('ebc87949-192c-48ca-d7a173193eaf'));
        Assert::assertFalse($this->rule->check([]));
    }
}
