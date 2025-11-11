<?php

declare(strict_types=1);

namespace Qubus\Validation\Rules;

use Qubus\Validation\Rule;

class Numeric extends Rule
{
    protected string $message = "The :attribute must be numeric";

    /**
     * Check the $value is valid
     *
     * @param mixed $value
     * @return bool
     */
    public function check(mixed $value): bool
    {
        return is_numeric($value);
    }
}
