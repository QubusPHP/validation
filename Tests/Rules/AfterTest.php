<?php

declare(strict_types=1);

namespace Qubus\Tests\Validation\Rules;

use Exception;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\DataProvider;
use Qubus\Validation\Rules\After;
use PHPUnit\Framework\TestCase;
use DateTime;

class AfterTest extends TestCase
{
    protected ?After $validator = null;

    public function setUp(): void
    {
        $this->validator = new After();
    }

    #[DataProvider('getInvalidDates')]
    public function testOnlyAWellFormedDateCanBeValidated($date)
    {
        $this->expectException(Exception::class);
        Assert::assertTrue(
            $this->validator->fillParameters(["3 years ago"])->check($date)
        );
    }

    #[DataProvider('getInvalidDates')]
    public function testANonWellFormedDateCannotBeValidated($date)
    {
        $this->expectException(Exception::class);
        $this->validator->fillParameters(["tomorrow"])->check($date);
    }

    public function testUserProvidedParamCannotBeValidatedBecauseItIsInvalid()
    {
        $this->expectException(Exception::class);
        $this->validator->fillParameters(["to,morrow"])->check("now");
    }

    public static function getInvalidDates(): array
    {
        $now = new DateTime();

        return [
            [12], //12 instead of 2012
            ["09"], //like '09 instead of 2009
            [$now->format("Y m d")],
            [$now->format("Y m d h:i:s")],
            ["tommorow"], //typo
            ["lasst year"] //typo
        ];
    }

    public function getValidDates(): array
    {
        $now = new DateTime();

        return [
            [2016],
            [$now->format("Y-m-d")],
            [$now->format("Y-m-d h:i:s")],
            ["now"],
            ["tomorrow"],
            ["2 years ago"]
        ];
    }

    public function testProvidedDateFailsValidation()
    {
        $now = new DateTime("today")->format("Y-m-d");
        $today = "today";

        Assert::assertFalse(
            $this->validator->fillParameters(['tomorrow'])->check($now)
        );

        Assert::assertFalse(
            $this->validator->fillParameters(['tomorrow'])->check($today)
        );
    }
}
