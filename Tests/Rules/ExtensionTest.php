<?php

declare(strict_types=1);

namespace Qubus\Tests\Validation\Rules;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Qubus\Validation\Rules\Extension;

class ExtensionTest extends TestCase
{

    public function setUp(): void
    {
        $this->rule = new Extension;
    }

    public function testValids()
    {
        Assert::assertTrue($this->rule->fillParameters(['pdf','png','txt'])->check('somefile.txt'));
        Assert::assertTrue($this->rule->fillParameters(['.pdf','.png','.txt'])->check('somefile.txt'));
        Assert::assertTrue($this->rule->fillParameters(['pdf','png','txt'])->check('path/to/somefile.txt'));
        Assert::assertTrue($this->rule->fillParameters(['pdf','png','txt'])->check('./absolute/path/to/somefile.txt'));
        Assert::assertTrue($this->rule->fillParameters(['pdf','png','txt'])->check('https://site.test/somefile.txt'));
    }

    public function testInvalids()
    {
        Assert::assertFalse($this->rule->fillParameters(['pdf','png','txt'])->check(''));
        Assert::assertFalse($this->rule->fillParameters(['pdf','png','txt'])->check('.dotfile'));
        Assert::assertFalse($this->rule->fillParameters(['pdf','png','txt'])->check('notafile'));
        Assert::assertFalse($this->rule->fillParameters(['pdf','png','txt'])->check('somefile.php'));
        Assert::assertFalse($this->rule->fillParameters(['.pdf','.png','.txt'])->check('somefile.php'));
    }
}
