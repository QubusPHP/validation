<?php

declare(strict_types=1);

namespace Qubus\Validation\Factories;

use Exception;
use Qubus\Validation\Validation;
use Qubus\Validation\Validator;

final class ValidationFactory
{
    /**
     * @throws Exception
     */
    public static function make(array $inputs, array $rules, array $messages = []): Validation
    {
        return new Validator()->make($inputs, $rules, $messages);
    }
}
