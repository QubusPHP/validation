<?php

declare(strict_types=1);

namespace Qubus\Validation\Rules;

use Qubus\Validation\MissingRequiredParameterException;
use Qubus\Validation\Rule;

class Date extends Rule
{
    protected string $message = "The :attribute is not valid date format";

    protected array $fillableParams = ['format'];

    protected array $params = [
        'format' => 'Y-m-d'
    ];

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

        $format = $this->parameter('format');
        return date_create_from_format($format, $value ?? '') !== false;
    }
}
