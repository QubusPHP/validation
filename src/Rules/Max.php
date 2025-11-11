<?php

declare(strict_types=1);

namespace Qubus\Validation\Rules;

use Qubus\Validation\MissingRequiredParameterException;
use Qubus\Validation\Rule;
use Qubus\Validation\Rules\Traits\SizeAware;

class Max extends Rule
{
    use SizeAware;

    protected string $message = "The :attribute maximum is :max";

    protected array $fillableParams = ['max'];

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

        $max = $this->getBytesSize($this->parameter('max'));
        $valueSize = $this->getValueSize($value);

        if (!is_numeric($valueSize)) {
            return false;
        }

        return $valueSize <= $max;
    }
}
