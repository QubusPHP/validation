<?php

declare(strict_types=1);

namespace Qubus\Validation\Rules;

use Qubus\Validation\Rule;

class Accepted extends Rule
{
    protected bool $implicit = true;

    protected string $message = "The :attribute must be accepted";

    /**
     * Check the $value is accepted.
     *
     * @param mixed $value
     * @return bool
     */
    public function check(mixed $value): bool
    {
        $acceptables = ['yes', 'on', '1', 1, true, 'true'];
        return in_array($value, $acceptables, true);
    }
}
