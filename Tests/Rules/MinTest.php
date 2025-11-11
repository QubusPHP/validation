<?php

declare(strict_types=1);

namespace Qubus\Tests\Validation\Rules;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Qubus\Validation\Rules\Min;

class MinTest extends TestCase
{
    public function setUp(): void
    {
        $this->rule = new Min;
    }

    public function testValids()
    {
        Assert::assertTrue($this->rule->fillParameters([100])->check(123));
        Assert::assertTrue($this->rule->fillParameters([6])->check('foobar'));
        Assert::assertTrue($this->rule->fillParameters([3])->check([1,2,3]));
    }

    public function testInvalids()
    {
        Assert::assertFalse($this->rule->fillParameters([7])->check('foobar'));
        Assert::assertFalse($this->rule->fillParameters([4])->check([1,2,3]));
        Assert::assertFalse($this->rule->fillParameters([200])->check(123));

        Assert::assertFalse($this->rule->fillParameters([4])->check('мин'));
        Assert::assertFalse($this->rule->fillParameters([5])->check('كلمة'));
        Assert::assertFalse($this->rule->fillParameters([4])->check('ワード'));
        Assert::assertFalse($this->rule->fillParameters([2])->check('字'));
    }

    public function testUploadedFileValue()
    {
        $twoMega = 1024 * 1024 * 2;
        $sampleFile = [
            'name' => pathinfo(__FILE__, PATHINFO_BASENAME),
            'type' => 'text/plain',
            'size' => $twoMega,
            'tmp_name' => __FILE__,
            'error' => 0
        ];

        Assert::assertTrue($this->rule->fillParameters([$twoMega])->check($sampleFile));
        Assert::assertTrue($this->rule->fillParameters(['2M'])->check($sampleFile));

        Assert::assertFalse($this->rule->fillParameters([$twoMega + 1])->check($sampleFile));
        Assert::assertFalse($this->rule->fillParameters(['2.1M'])->check($sampleFile));
    }
}
