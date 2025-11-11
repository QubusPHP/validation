<?php

declare(strict_types=1);

namespace Qubus\Validation\Rules;

use Qubus\Validation\Rule;

class AlphaNum extends Rule
{
    protected string $message = "The :attribute only allows alphabet and numeric";

    /**
     * Check the $value is valid.
     *
     * @param mixed $value
     * @return bool
     */
    public function check(mixed $value): bool
    {
        if (! is_string($value) && ! is_numeric($value)) {
            return false;
        }

        return preg_match('/^[\pL\pM\pN]+$/u', $value) > 0;
    }
}
