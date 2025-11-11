<?php

declare(strict_types=1);

namespace Qubus\Validation\Rules;

use Qubus\Exception\Data\TypeException;
use Qubus\Validation\MissingRequiredParameterException;
use Qubus\Validation\Rule;

class Different extends Rule
{
    protected string $message = "The :attribute must be different with :field";

    protected array $fillableParams = ['field'];

    /**
     * Check the $value is valid
     *
     * @param mixed $value
     * @return bool
     * @throws MissingRequiredParameterException
     * @throws TypeException
     */
    public function check(mixed $value): bool
    {
        $this->requireParameters($this->fillableParams);

        $field = $this->parameter('field');
        $anotherValue = $this->validation->getValue($field);

        return $value !== $anotherValue;
    }
}
