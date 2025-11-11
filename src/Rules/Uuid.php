<?php

declare(strict_types=1);

namespace Qubus\Validation\Rules;

use Qubus\Validation\Rule;

class Uuid extends Rule
{
    protected string $message = 'The :attribute is not a valid UUID or is NIL';

    /**
     * Check the $value is valid.
     *
     * @param mixed $value
     * @return bool
     */
    public function check(mixed $value): bool
    {
        return \Ramsey\Uuid\Uuid::isValid($value) && $value !== \Ramsey\Uuid\Uuid::NIL;
    }
}
