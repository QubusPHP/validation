<?php

declare(strict_types=1);

namespace Qubus\Validation\Rules;

use Qubus\Validation\MissingRequiredParameterException;
use Qubus\Validation\Rule;
use Qubus\Validation\Rules\Interfaces\ModifyValue;

class Defaults extends Rule implements ModifyValue
{
    protected string $message = "The :attribute default is :default";

    protected array $fillableParams = ['default'];

    /**
     * Check the $value is valid.
     *
     * @param mixed $value
     * @return bool
     * @throws MissingRequiredParameterException
     */
    public function check(mixed $value): bool
    {
        $this->requireParameters($this->fillableParams);

        $default = $this->parameter('default');
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function modifyValue(mixed $value): mixed
    {
        return $this->isEmptyValue($value) ? $this->parameter('default') : $value;
    }

    /**
     * Check $value is an empty value
     *
     * @param mixed $value
     * @return bool
     */
    protected function isEmptyValue(mixed $value): bool
    {
        $requiredValidator = new Required();
        return false === $requiredValidator->check($value);
    }
}
