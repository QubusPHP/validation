<?php

declare(strict_types=1);

namespace Qubus\Validation\Rules;

use Qubus\Validation\MissingRequiredParameterException;
use Qubus\Validation\Rule;

class DigitsBetween extends Rule
{
    protected string $message = "The :attribute must have a length between the given :min and :max";

    protected array $fillableParams = ['min', 'max'];

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

        $min = (int) $this->parameter('min');
        $max = (int) $this->parameter('max');

        $length = strlen((string) $value);

        return ! preg_match('/[^0-9]/', (string) $value)
        && $length >= $min && $length <= $max;
    }
}
