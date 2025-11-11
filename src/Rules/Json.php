<?php

declare(strict_types=1);

namespace Qubus\Validation\Rules;

use Qubus\Validation\Rule;

class Json extends Rule
{
    protected string $message = "The :attribute must be a valid JSON string";

    /**
     * Check the $value is valid
     *
     * @param mixed $value
     * @return bool
     */
    public function check(mixed $value): bool
    {
        if (! is_string($value) || empty($value)) {
            return false;
        }

        if (!json_validate($value)) {
            return false;
        }

        return true;
    }
}
