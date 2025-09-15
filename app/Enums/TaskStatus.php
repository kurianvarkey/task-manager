<?php

declare(strict_types=1);

namespace App\Enums;

use App\Enums\Traits\EnumCommonFunctions;

enum TaskStatus: int
{
    use EnumCommonFunctions;

    case Pending = 1;
    case InProgress = 2;
    case Completed = 3;

    /**
     * To String will return the name of the role
     */
    public function toString(): string
    {
        return match ($this) {
            self::Pending => 'pending',
            self::InProgress => 'inprogress',
            self::Completed => 'completed',
            default => '',
        };
    }
}
