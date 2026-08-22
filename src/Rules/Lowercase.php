<?php

declare(strict_types=1);

namespace Qubus\Validation\Rules;

use Qubus\Validation\Rule;

class Lowercase extends Rule
{
    protected string $message = "The :attribute must be lowercase";

    /**
     * Check the $value is valid
     *
     * @param mixed $value
     * @return bool
     */
    public function check(mixed $value): bool
    {
        if (!is_string($value)) {
            return false;
        }

        $encoding = mb_detect_encoding($value) ?: 'UTF-8';
        return mb_strtolower($value, $encoding) === $value;
    }
}
