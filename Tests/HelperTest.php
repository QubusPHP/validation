<?php

declare(strict_types=1);

namespace Qubus\Tests\Validation;

use PHPUnit\Framework\Assert;
use Qubus\Validation\Helper;
use PHPUnit\Framework\TestCase;

class HelperTest extends TestCase
{
    public function testArrayHas()
    {
        $array = [
            'foo' => [
                'bar' => [
                    'baz' => null
                ]
            ],
            'one.two.three' => null
        ];

        Assert::assertTrue(Helper::arrayHas($array, 'foo'));
        Assert::assertTrue(Helper::arrayHas($array, 'foo.bar'));
        Assert::assertTrue(Helper::arrayHas($array, 'foo.bar.baz'));
        Assert::assertTrue(Helper::arrayHas($array, 'one.two.three'));

        Assert::assertFalse(Helper::arrayHas($array, 'foo.baz'));
        Assert::assertFalse(Helper::arrayHas($array, 'bar.baz'));
        Assert::assertFalse(Helper::arrayHas($array, 'foo.bar.qux'));
        Assert::assertFalse(Helper::arrayHas($array, 'one.two'));
    }

    public function testArrayGet()
    {
        $array = [
            'foo' => [
                'bar' => [
                    'baz' => 'abc'
                ]
            ],
            'one.two.three' => 123
        ];

        Assert::assertEquals($array['foo'], Helper::arrayGet($array, 'foo'));
        Assert::assertEquals($array['foo']['bar'], Helper::arrayGet($array, 'foo.bar'));
        Assert::assertEquals($array['foo']['bar']['baz'], Helper::arrayGet($array, 'foo.bar.baz'));
        Assert::assertEquals(123, Helper::arrayGet($array, 'one.two.three'));

        Assert::assertNull(Helper::arrayGet($array, 'foo.bar.baz.qux'));
        Assert::assertNull(Helper::arrayGet($array, 'one.two'));
    }

    public function testArrayDot()
    {
        $array = [
            'foo' => [
                'bar' => [
                    'baz' => 123,
                    'qux' => 456
                ]
            ],
            'comments' => [
                ['id' => 1, 'text' => 'foo'],
                ['id' => 2, 'text' => 'bar'],
                ['id' => 3, 'text' => 'baz'],
            ],
            'one.two.three' => 789
        ];

        Assert::assertEquals([
            'foo.bar.baz' => 123,
            'foo.bar.qux' => 456,
            'comments.0.id' => 1,
            'comments.0.text' => 'foo',
            'comments.1.id' => 2,
            'comments.1.text' => 'bar',
            'comments.2.id' => 3,
            'comments.2.text' => 'baz',
            'one.two.three' => 789
        ], Helper::arrayDot($array));
    }

    public function testArraySet()
    {
        $array = [
            'comments' => [
                ['text' => 'foo'],
                ['id' => 2, 'text' => 'bar'],
                ['id' => 3, 'text' => 'baz'],
            ]
        ];

        Helper::arraySet($array, 'comments.*.id', null, false);
        Helper::arraySet($array, 'comments.*.x.y', 1, false);

        Assert::assertEquals([
            'comments' => [
                ['id' => null, 'text' => 'foo', 'x' => ['y' => 1]],
                ['id' => 2, 'text' => 'bar', 'x' => ['y' => 1]],
                ['id' => 3, 'text' => 'baz', 'x' => ['y' => 1]],
            ]
        ], $array);
    }

    public function testArrayUnset()
    {
        $array = [
            'users' => [
                'one' => 'user_one',
                'two' => 'user_two',
            ],
            'stuffs' => [1, 'two', ['three'], null, false, true],
            'message' => "lorem ipsum",
        ];

        Helper::arrayUnset($array, 'users.one');
        Assert::assertEquals([
            'users' => [
                'two' => 'user_two',
            ],
            'stuffs' => [1, 'two', ['three'], null, false, true],
            'message' => "lorem ipsum",
        ], $array);

        Helper::arrayUnset($array, 'stuffs.*');
        Assert::assertEquals([
            'users' => [
                'two' => 'user_two',
            ],
            'stuffs' => [],
            'message' => "lorem ipsum",
        ], $array);
    }

    public function testJoin()
    {
        $pieces0 = [];
        $pieces1 = [1];
        $pieces2 = [1, 2];
        $pieces3 = [1, 2, 3];

        $separator = ', ';
        $lastSeparator = ', and ';

        Assert::assertEquals('', Helper::join($pieces0, $separator, $lastSeparator));
        Assert::assertEquals('1', Helper::join($pieces1, $separator, $lastSeparator));
        Assert::assertEquals('1, and 2', Helper::join($pieces2, $separator, $lastSeparator));
        Assert::assertEquals('1, 2, and 3', Helper::join($pieces3, $separator, $lastSeparator));
    }

    public function testWraps()
    {
        $inputs = [1, 2, 3];

        Assert::assertEquals(['-1-', '-2-', '-3-'], Helper::wraps($inputs, '-'));
        Assert::assertEquals(['-1+', '-2+', '-3+'], Helper::wraps($inputs, '-', '+'));
    }
}
