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
        return preg_match($regex, $value) > 0;
    }
}
