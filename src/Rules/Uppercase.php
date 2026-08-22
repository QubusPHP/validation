<?php

declare(strict_types=1);

namespace Qubus\Validation\Rules;

use Qubus\Validation\Rule;

class Uppercase extends Rule
{
    protected string $message = "The :attribute must be uppercase";

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
        return mb_strtoupper($value, $encoding) === $value;
    }
}
