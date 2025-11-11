<?php

declare(strict_types=1);

namespace Qubus\Tests\Validation\Rules;

use PHPUnit\Framework\Assert;
use Qubus\Validation\Rules\Between;
use PHPUnit\Framework\TestCase;

use const PATHINFO_BASENAME;

class BetweenTest extends TestCase
{
    public function setUp(): void
    {
        $this->rule = new Between();
    }

    public function testValids()
    {
        Assert::assertTrue($this->rule->fillParameters([6, 10])->check('foobar'));
        Assert::assertTrue($this->rule->fillParameters([6, 10])->check('футбол'));
        Assert::assertTrue($this->rule->fillParameters([2, 3])->check([1,2,3]));
        Assert::assertTrue($this->rule->fillParameters([100, 150])->check(123));
        Assert::assertTrue($this->rule->fillParameters([100, 150])->check(123.4));
    }

    public function testInvalids()
    {
        Assert::assertFalse($this->rule->fillParameters([2, 5])->check('foobar'));
        Assert::assertFalse($this->rule->fillParameters([2, 5])->check('футбол'));
        Assert::assertFalse($this->rule->fillParameters([4, 6])->check([1,2,3]));
        Assert::assertFalse($this->rule->fillParameters([50, 100])->check(123));
        Assert::assertFalse($this->rule->fillParameters([50, 100])->check(123.4));
    }

    public function testUploadedFileValue()
    {
        $mb = function ($n) {
            return $n * 1024 * 1024;
        };

        $sampleFile = [
            'name' => pathinfo(__FILE__, PATHINFO_BASENAME),
            'type' => 'text/plain',
            'size' => $mb(2),
            'tmp_name' => __FILE__,
            'error' => 0
        ];

        Assert::assertTrue($this->rule->fillParameters([$mb(2), $mb(5)])->check($sampleFile));
        Assert::assertTrue($this->rule->fillParameters(['2M', '5M'])->check($sampleFile));
        Assert::assertTrue($this->rule->fillParameters([$mb(1), $mb(2)])->check($sampleFile));
        Assert::assertTrue($this->rule->fillParameters(['1M', '2M'])->check($sampleFile));

        Assert::assertFalse($this->rule->fillParameters([$mb(2.1), $mb(5)])->check($sampleFile));
        Assert::assertFalse($this->rule->fillParameters(['2.1M', '5M'])->check($sampleFile));
        Assert::assertFalse($this->rule->fillParameters([$mb(1), $mb(1.9)])->check($sampleFile));
        Assert::assertFalse($this->rule->fillParameters(['1M', '1.9M'])->check($sampleFile));
    }
}
