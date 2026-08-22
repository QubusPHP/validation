<?php

declare(strict_types=1);

namespace Qubus\Tests\Validation;

use Countable;
use PHPUnit\Framework\TestCase;
use Qubus\Validation\ErrorBag;
use Qubus\Validation\MissingRequiredParameterException;
use Qubus\Validation\Validator;

class RegressionTest extends TestCase
{
    public function testRepeatedValidationReplacesPreviousResultState(): void
    {
        $validation = new Validator()->make(
            ['value' => 'invalid'],
            ['value' => 'required|email']
        );

        $validation->validate();
        self::assertTrue($validation->fails());
        self::assertSame(['value' => 'invalid'], $validation->getInvalidData());

        $validation->validate(['value' => 'valid@example.com']);

        self::assertTrue($validation->passes());
        self::assertSame(['value' => 'valid@example.com'], $validation->getValidData());
        self::assertSame([], $validation->getInvalidData());
    }

    public function testRepeatedValidationResetsConditionalRequiredState(): void
    {
        $validation = new Validator()->make(
            ['condition' => 'yes'],
            ['email' => 'required_if:condition,yes|email']
        );

        $validation->validate();
        self::assertTrue($validation->fails());

        $validation->validate(['condition' => 'no']);

        self::assertTrue($validation->passes());
    }

    public function testRepeatedRootArrayValidationReplacesNumericKeys(): void
    {
        $validation = new Validator()->make(
            ['invalid'],
            ['*' => 'email']
        );

        $validation->validate();
        self::assertTrue($validation->fails());

        $validation->validate([0 => 'valid@example.com']);

        self::assertTrue($validation->passes());
        self::assertSame([0 => 'valid@example.com'], $validation->getValidData());
    }

    public function testValidatedDataKeepsValidAndInvalidNestedSiblings(): void
    {
        $validation = new Validator()->validate(
            ['user' => ['name' => 'Ada', 'email' => 'invalid']],
            ['user.name' => 'required', 'user.email' => 'email']
        );

        self::assertSame(
            ['user' => ['name' => 'Ada', 'email' => 'invalid']],
            $validation->getValidatedData()
        );
    }

    public function testWildcardDependentRulesResolveSiblingKeys(): void
    {
        $validation = new Validator()->validate(
            [
                'users' => [
                    ['trigger' => true, 'password' => 'same', 'confirmation' => 'same'],
                    ['password' => 'first', 'confirmation' => 'second'],
                ],
            ],
            [
                'users.*.note' => 'required_with:users.*.trigger',
                'users.*.confirmation' => 'different:users.*.password',
            ]
        );

        self::assertTrue($validation->errors()->has('users.0.note:required_with'));
        self::assertFalse($validation->errors()->has('users.1.note:required_with'));
        self::assertTrue($validation->errors()->has('users.0.confirmation:different'));
        self::assertFalse($validation->errors()->has('users.1.confirmation:different'));
    }

    public function testInputAliasesMayContainColons(): void
    {
        $validation = new Validator()->validate(
            ['email:Primary: address' => 'invalid'],
            ['email' => 'email']
        );

        self::assertSame(
            'The Primary: address is not valid email',
            $validation->errors()->first('email')
        );
    }

    public function testErrorBagSupportsNativeCount(): void
    {
        $errors = new ErrorBag(['email' => ['required' => 'Required']]);

        self::assertInstanceOf(Countable::class, $errors);
        self::assertCount(1, $errors);
    }

    public function testRegexWithoutPatternReportsMissingConfiguration(): void
    {
        $this->expectException(MissingRequiredParameterException::class);

        new Validator()->validate(['value' => 'test'], ['value' => 'regex']);
    }

    public function testIntegerAliasUsesNumericValueForSizeRules(): void
    {
        $validation = new Validator()->validate(
            ['value' => '12'],
            ['value' => 'int|min:10']
        );

        self::assertTrue($validation->passes());
    }
}
