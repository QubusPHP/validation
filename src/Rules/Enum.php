<?php

declare(strict_types=1);

namespace Qubus\Validation\Rules;

use Qubus\Validation\Rule;
use UnitEnum;

use function Qubus\Support\Helpers\is_null__;

class Enum extends Rule
{
    protected string $message = "The :attribute is not valid Enum.";

    /**
     * Check $value is valid
     *
     * @param string|UnitEnum $value
     * @return bool
     */
    public function check(mixed $value): bool
    {
        if ((is_string($value) && ! enum_exists($value)) || ! method_exists($value, 'tryFrom')) {
            return false;
        }

        return true;
    }
}
