<?php

declare(strict_types=1);

namespace Qubus\Validation;

use Qubus\Validation\MessageBag;

interface Validatable
{
    /**
     * Determine if the data passes the validation rules.
     */
    public function passes(): bool;

    /**
     * Determine if the data fails the validation rules.
     */
    public function fails(): bool;

    /**
     * Get the failed validation rules.
     *
     * @return array
     */
    public function failed(): array;

    /**
     * Get the message container for the validator.
     */
    public function messages(): MessageBag;

    /**
     * An alternative more semantic shortcut to the message container.
     */
    public function errors(): MessageBag;
}
