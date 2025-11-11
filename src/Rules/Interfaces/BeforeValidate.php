<?php

declare(strict_types=1);

namespace Qubus\Validation\Rules\Interfaces;

interface BeforeValidate
{
    /**
     * Before validate hook.
     *
     * @return void
     */
    public function beforeValidate(): void;
}
