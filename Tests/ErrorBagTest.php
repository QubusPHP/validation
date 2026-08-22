<?php

namespace Qubus\Tests\Validation;

use PHPUnit\Framework\Assert;
use Qubus\Validation\ErrorBag;
use PHPUnit\Framework\TestCase;

class ErrorBagTest extends TestCase
{

    public function testCount()
    {
        $errors = new ErrorBag([
            'email' => [
                'email' => 'foo',
                'unique' => 'bar',
            ],
            'age' => [
                'numeric' => 'baz',
                'min' => 'qux'
            ]
        ]);

        Assert::assertEquals(4, $errors->count());
    }

    public function testAdd()
    {
        $errors = new ErrorBag();

        $errors->add('email', 'email', 'foo');
        $errors->add('email', 'unique', 'bar');
        $errors->add('age', 'numeric', 'baz');
        $errors->add('age', 'min', 'qux');

        Assert::assertEquals([
            'email' => [
                'email' => 'foo',
                'unique' => 'bar',
            ],
            'age' => [
                'numeric' => 'baz',
                'min' => 'qux'
            ]
        ], $errors->toArray());
    }

    public function testHas()
    {
        $errors = new ErrorBag([
            'email' => [
                'email' => 'foo',
                'unique' => 'bar',
            ],
            'items.0.id_product' => [
                'numeric' => 'qwerty'
            ],
            'items.1.id_product' => [
                'numeric' => 'qwerty'
            ],
            'items.2.id_product' => [
                'numeric' => 'qwerty'
            ],
        ]);

        Assert::assertTrue($errors->has('email'));
        Assert::assertTrue($errors->has('email:unique'));
        Assert::assertTrue($errors->has('email:email'));
        Assert::assertTrue($errors->has('items.0.*'));
        Assert::assertTrue($errors->has('items.*.id_product'));
        Assert::assertTrue($errors->has('items.0.*:numeric'));
        Assert::assertTrue($errors->has('items.*.id_product:numeric'));

        Assert::assertFalse($errors->has('not_exists'));
        Assert::assertFalse($errors->has('email:unregistered_rule'));
        Assert::assertFalse($errors->has('items.3.*'));
        Assert::assertFalse($errors->has('items.*.not_exists'));
        Assert::assertFalse($errors->has('items.0.*:unregistered_rule'));
    }

    public function testWildcardMatchesOnePathSegment(): void
    {
        $errors = new ErrorBag([
            'items.0.name' => ['required' => 'Direct child'],
            'items.0.profile.name' => ['required' => 'Nested child'],
        ]);

        Assert::assertSame(
            ['items.0.name' => ['required' => 'Direct child']],
            $errors->get('items.*.name')
        );
    }

    public function testFirst()
    {
        $errors = new ErrorBag([
            'email' => [
                'email' => '1',
                'unique' => '2',
            ],
            'items.0.id_product' => [
                'numeric' => '3'
            ],
            'items.1.id_product' => [
                'numeric' => '4'
            ],
            'items.2.id_product' => [
                'numeric' => '5'
            ],
        ]);

        Assert::assertEquals('1', $errors->first('email'));
        Assert::assertEquals('1', $errors->first('email:email'));
        Assert::assertEquals('2', $errors->first('email:unique'));

        Assert::assertEquals('3', $errors->first('items.*'));
        Assert::assertEquals('3', $errors->first('items.*.id_product'));
        Assert::assertEquals('3', $errors->first('items.0.*'));
        Assert::assertEquals('3', $errors->first('items.0.*:numeric'));
        Assert::assertEquals('4', $errors->first('items.1.*'));

        Assert::assertNull($errors->first('not_exists'));
        Assert::assertNull($errors->first('email:unregistered_rule'));
        Assert::assertNull($errors->first('items.99.*'));
        Assert::assertNull($errors->first('items.*.not_exists'));
        Assert::assertNull($errors->first('items.1.id_product:unregistered_rule'));
    }

    public function testGet()
    {
        $errors = new ErrorBag([
            'email' => [
                'email' => '1',
                'unique' => '2',
            ],

            'items.0.id_product' => [
                'numeric' => '3',
                'etc' => 'x'
            ],
            'items.0.qty' => [
                'numeric' => 'a'
            ],

            'items.1.id_product' => [
                'numeric' => '4',
                'etc' => 'y'
            ],
            'items.1.qty' => [
                'numeric' => 'b'
            ]
        ]);

        Assert::assertEquals([
            'email' => 'prefix 1 suffix',
            'unique' => 'prefix 2 suffix'
        ], $errors->get('email', 'prefix :message suffix'));

        Assert::assertEquals([
            'email' => 'prefix 1 suffix',
        ], $errors->get('email:email', 'prefix :message suffix'));

        Assert::assertEquals([
            'items.0.id_product' => [
                'numeric' => 'prefix 3 suffix',
                'etc' => 'prefix x suffix',
            ],
            'items.0.qty' => [
                'numeric' => 'prefix a suffix',
            ],
            'items.1.id_product' => [
                'numeric' => 'prefix 4 suffix',
                'etc' => 'prefix y suffix',
            ],
            'items.1.qty' => [
                'numeric' => 'prefix b suffix',
            ]
        ], $errors->get('items.*', 'prefix :message suffix'));

        Assert::assertEquals([
            'items.0.id_product' => [
                'numeric' => 'prefix 3 suffix',
                'etc' => 'prefix x suffix'
            ],
            'items.0.qty' => [
                'numeric' => 'prefix a suffix',
            ]
        ], $errors->get('items.0.*', 'prefix :message suffix'));

        Assert::assertEquals([
            'items.0.id_product' => [
                'numeric' => 'prefix 3 suffix',
                'etc' => 'prefix x suffix'
            ],
            'items.1.id_product' => [
                'numeric' => 'prefix 4 suffix',
                'etc' => 'prefix y suffix'
            ]
        ], $errors->get('items.*.id_product', 'prefix :message suffix'));

        Assert::assertEquals([
            'items.0.id_product' => [
                'etc' => 'prefix x suffix'
            ],
            'items.1.id_product' => [
                'etc' => 'prefix y suffix'
            ]
        ], $errors->get('items.*.id_product:etc', 'prefix :message suffix'));

        Assert::assertEquals([
            'items.0.id_product' => [
                'etc' => 'prefix x suffix'
            ],
            'items.1.id_product' => [
                'etc' => 'prefix y suffix'
            ]
        ], $errors->get('items.*:etc', 'prefix :message suffix'));
    }

    public function testAll()
    {
        $errors = new ErrorBag([
            'email' => [
                'email' => '1',
                'unique' => '2',
            ],
            'items.0.id_product' => [
                'numeric' => '3',
                'etc' => 'x'
            ],
            'items.0.qty' => [
                'numeric' => 'a'
            ],
            'items.1.id_product' => [
                'numeric' => '4',
                'etc' => 'y'
            ],
            'items.1.qty' => [
                'numeric' => 'b'
            ]
        ]);

        Assert::assertEquals([
            'prefix 1 suffix',
            'prefix 2 suffix',

            'prefix 3 suffix',
            'prefix x suffix',
            'prefix a suffix',

            'prefix 4 suffix',
            'prefix y suffix',
            'prefix b suffix',
        ], $errors->all('prefix :message suffix'));
    }

    public function testFirstOfAll()
    {
        $errors = new ErrorBag([
            'email' => [
                'email' => '1',
                'unique' => '2',
            ],
            'items.0.id_product' => [
                'numeric' => '3',
                'etc' => 'x'
            ],
            'items.0.qty' => [
                'numeric' => 'a'
            ],
            'items.1.id_product' => [
                'numeric' => '4',
                'etc' => 'y'
            ],
            'items.1.qty' => [
                'numeric' => 'b'
            ]
        ]);

        Assert::assertEquals([
            'email' => 'prefix 1 suffix',
            'items' => [
                [
                    'id_product' => 'prefix 3 suffix',
                    'qty' => 'prefix a suffix'
                ],
                [
                    'id_product' => 'prefix 4 suffix',
                    'qty' => 'prefix b suffix'
                ],
            ]
        ], $errors->firstOfAll('prefix :message suffix'));
    }

    public function testFirstOfAllDotNotation()
    {
        $errors = new ErrorBag([
            'email' => [
                'email' => '1',
                'unique' => '2',
            ],
            'items.0.id_product' => [
                'numeric' => '3',
                'etc' => 'x'
            ],
            'items.0.qty' => [
                'numeric' => 'a'
            ],
            'items.1.id_product' => [
                'numeric' => '4',
                'etc' => 'y'
            ],
            'items.1.qty' => [
                'numeric' => 'b'
            ]
        ]);

        Assert::assertEquals([
            'email' => 'prefix 1 suffix',
            'items.0.id_product' => 'prefix 3 suffix',
            'items.0.qty' => 'prefix a suffix',
            'items.1.id_product' => 'prefix 4 suffix',
            'items.1.qty' => 'prefix b suffix',
        ], $errors->firstOfAll('prefix :message suffix', true));
    }
}
