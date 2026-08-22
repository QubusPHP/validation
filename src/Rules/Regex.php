<?php

declare(strict_types=1);

namespace Qubus\Validation\Rules;

use Qubus\Validation\MissingRequiredParameterException;
use Qubus\Validation\Rule;

class Regex extends Rule
{
    protected string $message = "The :attribute is not valid format";

    protected array $fillableParams = ['regex'];

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
        $regex = $this->parameter('regex');
        if (!is_string($regex) || (!is_string($value) && !is_numeric($value))) {
            return false;
        }

        return @preg_match($regex, (string) $value) > 0;
    }
}
