<?php

declare(strict_types=1);

namespace Qubus\Validation\Rules;

use DateTimeImmutable;
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
        if (!is_string($format) || (!is_scalar($value) && !$value instanceof \Stringable)) {
            return false;
        }

        $date = DateTimeImmutable::createFromFormat($format, (string) $value);
        $errors = DateTimeImmutable::getLastErrors();

        return $date !== false
        && ($errors === false || ($errors['warning_count'] === 0 && $errors['error_count'] === 0));
    }
}
