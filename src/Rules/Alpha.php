<?php

declare(strict_types=1);

namespace Qubus\Validation\Rules;

use Qubus\Validation\Rule;

class Alpha extends Rule
{
    protected string $message = "The :attribute only allows alphabet characters";

    /**
     * Check the $value is valid.
     *
     * @param mixed $value
     * @return bool
     */
    public function check(mixed $value): bool
    {
        return is_string($value) && preg_match('/^[\pL\pM]+$/u', $value);
    }
}
