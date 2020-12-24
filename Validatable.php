<?php

/**
 * Qubus\Validation
 *
 * @link       https://github.com/QubusPHP/validation
 * @copyright  2020 Joshua Parker
 * @license    https://opensource.org/licenses/mit-license.php MIT License
 *
 * @since      1.0.0
 */

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
