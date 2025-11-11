<?php

declare(strict_types=1);

namespace Qubus\Validation\Rules;

use Qubus\Validation\Rule;

use function preg_match;

class Ulid extends Rule
{
    protected string $message = 'The :attribute is not a valid ULID.';

    /**
     * Check the $value is valid.
     *
     * @param mixed $value
     * @return bool
     */
    public function check(mixed $value): bool
    {
        if (!is_string($value)) {
            return false;
        }

        return (bool) preg_match('/^[0-9A-HJKMNP-TV-Z]{26}$/i', $value);
    }
}
