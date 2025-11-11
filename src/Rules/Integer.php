<?php

declare(strict_types=1);

namespace Qubus\Validation\Rules;

use Qubus\Validation\Rule;

class Integer extends Rule
{
    protected string $message = "The :attribute must be integer";

    /**
     * Check the $value is valid
     *
     * @param mixed $value
     * @return bool
     */
    public function check(mixed $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_INT) !== false;
    }
}
