<?php

declare(strict_types=1);

namespace Qubus\Validation\Rules;

use Qubus\Validation\MissingRequiredParameterException;
use Qubus\Validation\Rule;

class Digits extends Rule
{
    protected string $message = "The :attribute must be numeric and must have an exact length of :length";

    protected array $fillableParams = ['length'];

    /**
     * Check the $value is valid
     *
     * @param mixed $value
     * @return bool
     * @throws MissingRequiredParameterException
     */
    public function check(mixed $value): bool
    {
        $this->requireParameters($this->fillableParams);

        $length = (int) $this->parameter('length');

        return ! preg_match('/[^0-9]/', (string) $value)
        && strlen((string) $value) == $length;
    }
}
