<?php

declare(strict_types=1);

namespace Qubus\Tests\Validation\Fixtures;

enum UserRole: string
{
    case USER = 'user';
    case ADMIN = 'admin';
    case MANAGER = 'manager';
}
