<?php

declare(strict_types=1);

namespace App\Enums;

use App\Enums\Traits\EnumCommonFunctions;

enum TaskPriority: int
{
    use EnumCommonFunctions;

    case Low = 1;
    case Medium = 2;
    case High = 3;

    /**
     * To String will return the name of the role
     */
    public function toString(): string
    {
        return match ($this) {
            self::Low => 'low',
            self::Medium => 'medium',
            self::High => 'high',
            default => '',
        };
    }
}
