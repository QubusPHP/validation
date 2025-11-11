<?php

declare(strict_types=1);

namespace Qubus\Validation\Rules;

use Qubus\Validation\Rule;

class Ipv4 extends Rule
{
    protected string $message = "The :attribute is not valid IPv4 Address";

    /**
     * Check the $value is valid
     *
     * @param mixed $value
     * @return bool
     */
    public function check(mixed $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false;
    }
}
