<?php

declare(strict_types=1);

namespace Qubus\Validation\Rules;

use Qubus\Validation\Rule;
use UnitEnum;

class Enum extends Rule
{
    protected string $message = "The :attribute is not valid Enum.";

    /**
     * Check $value is valid
     *
     * @param mixed $value
     * @return bool
     */
    public function check(mixed $value): bool
    {
        return $value instanceof UnitEnum
        || (is_string($value) && enum_exists($value));
    }
}
