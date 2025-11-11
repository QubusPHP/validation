<?php

declare(strict_types=1);

namespace Qubus\Tests\Validation;

use PHPUnit\Framework\Attributes\DataProvider;
use Qubus\Validation\Validation;
use Qubus\Validation\Validator;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionException;

class ValidationTest extends TestCase
{
    /**
     * @param string $rules
     * @param array $expectedResult
     * @throws ReflectionException
     */
    #[DataProvider('parseRuleProvider')]
    public function testParseRule(string $rules, array $expectedResult)
    {
        $class = new ReflectionClass(Validation::class);
        $method = $class->getMethod('parseRule');
        $method->setAccessible(true);

        $validation = new Validation(new Validator(), [], []);

        $result = $method->invokeArgs($validation, [$rules]);
        $this->assertSame($expectedResult, $result);
    }

    /**
     * @return array
     */
    public static function parseRuleProvider(): array
    {
        return [
            [
                'email',
                [
                    'email',
                    [],
                ],
            ],
            [
                'min:6',
                [
                    'min',
                    ['6'],
                ],
            ],
            [
                'uploaded_file:0,500K,png,jpeg',
                [
                    'uploaded_file',
                    ['0', '500K', 'png', 'jpeg'],
                ],
            ],
            [
                'same:password',
                [
                    'same',
                    ['password'],
                ],
            ],
            [
                'regex:/^([a-zA-Z\,]*)$/',
                [
                    'regex',
                    ['/^([a-zA-Z\,]*)$/'],
                ],
            ],
        ];
    }
}
