<?php

declare(strict_types=1);

namespace Qubus\Tests\Validation\Fixtures;

use Qubus\Validation\Rule;

class Required extends Rule
{
    public function check(mixed $value): bool
    {
        return true;
    }
}
