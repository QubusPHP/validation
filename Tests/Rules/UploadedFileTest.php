<?php

declare(strict_types=1);

namespace Qubus\Tests\Validation\Rules;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Qubus\Validation\Rules\UploadedFile;

class UploadedFileTest extends TestCase
{
    public function setUp(): void
    {
        $this->rule = new UploadedFile();
    }

    public function testValidUploadedFile()
    {
        $file = [
            'name' => pathinfo(__FILE__, PATHINFO_BASENAME),
            'type' => 'text/plain',
            'size' => filesize(__FILE__),
            'tmp_name' => __FILE__,
            'error' => UPLOAD_ERR_OK
        ];

        $uploadedFileRule = $this->getMockBuilder(UploadedFile::class)
            ->onlyMethods(['isUploadedFile'])
            ->getMock();

        $uploadedFileRule->expects($this->once())
            ->method('isUploadedFile')
            ->willReturn(true);

        Assert::assertTrue($uploadedFileRule->check($file));
    }

    /**
     * Make sure we can't just passing array like valid $_FILES['key']
     */
    public function testValidateWithoutMockShouldBeInvalid()
    {
        Assert::assertFalse($this->rule->check([
            'name' => pathinfo(__FILE__, PATHINFO_BASENAME),
            'type' => 'text/plain',
            'size' => filesize(__FILE__),
            'tmp_name' => __FILE__,
            'error' => UPLOAD_ERR_OK
        ]));
    }

    /**
     * Missing UPLOAD_ERR_NO_FILE should be valid because it is job for required rule
     */
    public function testEmptyUploadedFileShouldBeValid()
    {
        Assert::assertTrue($this->rule->check([
            'name' => '',
            'type' => '',
            'size' => '',
            'tmp_name' => '',
            'error' => UPLOAD_ERR_NO_FILE
        ]));
    }

    public function testUploadError()
    {
        Assert::assertFalse($this->rule->check([
            'name' => '',
            'type' => '',
            'size' => '',
            'tmp_name' => '',
            'error' => 5
        ]));
    }

    public function testMaxSize()
    {
        $rule = $this->getMockBuilder(UploadedFile::class)
            ->onlyMethods(['isUploadedFile'])
            ->getMock();

        $rule->expects($this->exactly(2))
            ->method('isUploadedFile')
            ->willReturn(true);

        $rule->maxSize("1MB");

        Assert::assertFalse($rule->check([
            'name' => pathinfo(__FILE__, PATHINFO_BASENAME),
            'type' => 'text/plain',
            'size' => 1024 * 1024 * 1.1,
            'tmp_name' => __FILE__,
            'error' => 0
        ]));

        Assert::assertTrue($rule->check([
            'name' => pathinfo(__FILE__, PATHINFO_BASENAME),
            'type' => 'text/plain',
            'size' => 1000000,
            'tmp_name' => __FILE__,
            'error' => 0
        ]));
    }

    public function testMinSize()
    {
        $rule = $this->getMockBuilder(UploadedFile::class)
            ->onlyMethods(['isUploadedFile'])
            ->getMock();

        $rule->expects($this->exactly(2))
            ->method('isUploadedFile')
            ->willReturn(true);

        $rule->minSize('10K');

        Assert::assertFalse($rule->check([
            'name' => pathinfo(__FILE__, PATHINFO_BASENAME),
            'type' => 'text/plain',
            'size' => 1024, // 1K
            'tmp_name' => __FILE__,
            'error' => 0
        ]));

        Assert::assertTrue($rule->check([
            'name' => pathinfo(__FILE__, PATHINFO_BASENAME),
            'type' => 'text/plain',
            'size' => 10 * 1024,
            'tmp_name' => __FILE__,
            'error' => 0
        ]));
    }

    public function testZeroMaximumSizeIsEnforced(): void
    {
        $rule = $this->getMockBuilder(UploadedFile::class)
            ->onlyMethods(['isUploadedFile'])
            ->getMock();

        $rule->expects($this->once())
            ->method('isUploadedFile')
            ->willReturn(true);

        $rule->maxSize(0);

        Assert::assertFalse($rule->check([
            'name' => 'file.txt',
            'type' => 'text/plain',
            'size' => 1,
            'tmp_name' => __FILE__,
            'error' => UPLOAD_ERR_OK,
        ]));
    }

    public function testFileTypes()
    {
        $rule = $this->getMockBuilder(UploadedFile::class)
            ->onlyMethods(['isUploadedFile'])
            ->getMock();

        $rule->expects($this->exactly(3))
            ->method('isUploadedFile')
            ->willReturn(true);

        $rule->fileTypes('png|jpeg');

        Assert::assertFalse($rule->check([
            'name' => pathinfo(__FILE__, PATHINFO_BASENAME),
            'type' => 'text/plain',
            'size' => 1024, // 1K
            'tmp_name' => __FILE__,
            'error' => 0
        ]));

        Assert::assertTrue($rule->check([
            'name' => pathinfo(__FILE__, PATHINFO_BASENAME),
            'type' => 'image/png',
            'size' => 10 * 1024,
            'tmp_name' => __FILE__,
            'error' => 0
        ]));

        Assert::assertTrue($rule->check([
            'name' => pathinfo(__FILE__, PATHINFO_BASENAME),
            'type' => 'image/jpeg',
            'size' => 10 * 1024,
            'tmp_name' => __FILE__,
            'error' => 0
        ]));
    }

    /**
     * Missing array key(s) should be valid because it is job for required rule
     */
    public function testMissingAKeyShouldBeValid()
    {
        // missing name
        Assert::assertTrue($this->rule->check([
            'type' => 'text/plain',
            'size' => filesize(__FILE__),
            'tmp_name' => __FILE__,
            'error' => 0
        ]));

        // missing type
        Assert::assertTrue($this->rule->check([
            'name' => pathinfo(__FILE__, PATHINFO_BASENAME),
            'size' => filesize(__FILE__),
            'tmp_name' => __FILE__,
            'error' => 0
        ]));

        // missing size
        Assert::assertTrue($this->rule->check([
            'name' => pathinfo(__FILE__, PATHINFO_BASENAME),
            'type' => 'text/plain',
            'tmp_name' => __FILE__,
            'error' => 0
        ]));

        // missing tmp_name
        Assert::assertTrue($this->rule->check([
            'name' => pathinfo(__FILE__, PATHINFO_BASENAME),
            'type' => 'text/plain',
            'size' => filesize(__FILE__),
            'error' => 0
        ]));
    }
}
