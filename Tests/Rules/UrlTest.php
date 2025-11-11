<?php

declare(strict_types=1);

namespace Qubus\Tests\Validation\Rules;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Qubus\Validation\Rules\Url;

class UrlTest extends TestCase
{
    public function setUp(): void
    {
        $this->rule = new Url;
    }

    public function testValids()
    {
        // Without specific schemes
        Assert::assertTrue($this->rule->check('ftp://foobar.com'));
        Assert::assertTrue($this->rule->check('any://foobar.com'));
        Assert::assertTrue($this->rule->check('http://foobar.com'));
        Assert::assertTrue($this->rule->check('https://foobar.com'));
        Assert::assertTrue($this->rule->check('https://foobar.com/path?a=123&b=blah'));

        // Using specific schemes
        Assert::assertTrue($this->rule->fillParameters(['ftp'])->check('ftp://foobar.com'));
        Assert::assertTrue($this->rule->fillParameters(['any'])->check('any://foobar.com'));
        Assert::assertTrue($this->rule->fillParameters(['http'])->check('http://foobar.com'));
        Assert::assertTrue($this->rule->fillParameters(['https'])->check('https://foobar.com'));
        Assert::assertTrue($this->rule->fillParameters(['http', 'https'])->check('https://foobar.com'));
        Assert::assertTrue($this->rule->fillParameters(['foo', 'bar'])->check('bar://foobar.com'));
        Assert::assertTrue($this->rule->fillParameters(['mailto'])->check('mailto:johndoe@gmail.com'));
        Assert::assertTrue($this->rule->fillParameters(['jdbc'])->check('jdbc:mysql://localhost/dbname'));

        // Using forScheme
        Assert::assertTrue($this->rule->forScheme('ftp')->check('ftp://foobar.com'));
        Assert::assertTrue($this->rule->forScheme('http')->check('http://foobar.com'));
        Assert::assertTrue($this->rule->forScheme('https')->check('https://foobar.com'));
        Assert::assertTrue($this->rule->forScheme(['http', 'https'])->check('https://foobar.com'));
        Assert::assertTrue($this->rule->forScheme('mailto')->check('mailto:johndoe@gmail.com'));
        Assert::assertTrue($this->rule->forScheme('jdbc')->check('jdbc:mysql://localhost/dbname'));
    }

    public function testInvalids()
    {
        Assert::assertFalse($this->rule->check('foo:'));
        Assert::assertFalse($this->rule->check('mailto:johndoe@gmail.com'));
        Assert::assertFalse($this->rule->forScheme('mailto')->check('http://www.foobar.com'));
        Assert::assertFalse($this->rule->forScheme('ftp')->check('http://www.foobar.com'));
        Assert::assertFalse($this->rule->forScheme('jdbc')->check('http://www.foobar.com'));
        Assert::assertFalse($this->rule->forScheme(['http', 'https'])->check('any://www.foobar.com'));
    }
}
