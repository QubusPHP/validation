<?php

declare(strict_types=1);

namespace Qubus\Tests\Validation\Rules;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Qubus\Validation\Rules\Ulid;

class UlidTest extends TestCase
{
    protected function setUp(): void
    {
        $this->rule = new Ulid();
    }

    public function testValids()
    {
        Assert::assertTrue($this->rule->check('01K9T2XG4SEFZTP45ZJDY0GKPQ'));
        Assert::assertTrue($this->rule->check('01K9T2YABHE2RV0969Y65BVE4K'));
    }

    public function testInvalids()
    {
        Assert::assertFalse($this->rule->check('1'));
        Assert::assertFalse($this->rule->check('01K9T2ZFJBE3QAPDB0!0K9M6WF'));
        Assert::assertFalse($this->rule->check('ebc87949-192c-48ca-9822-d7a173193eaf'));
    }
}
