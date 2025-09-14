<?php

declare(strict_types=1);

namespace App\Enums;

use App\Enums\Traits\EnumCommonFunctions;

enum Role: int
{
    use EnumCommonFunctions;

    case Admin = 1; // Admin
    case User = 2; // User

    /**
     * To String will return the name of the role
     */
    public function toString(): string
    {
        return match ($this) {
            self::Admin => 'Admin',
            self::User => 'User',
            default => '',
        };
    }
}
