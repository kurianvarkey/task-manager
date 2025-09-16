<?php

declare(strict_types=1);

namespace App\Enums;

use App\Enums\Traits\EnumCommonFunctions;

enum OperationType: int
{
    use EnumCommonFunctions;

    case Created = 1;
    case Updated = 2;
    case Deleted = 3;
    case Restored = 4;

    /**
     * Convert enum to string
     */
    public function toString(): string
    {
        return match ($this) {
            self::Created => 'created',
            self::Updated => 'updated',
            self::Deleted => 'deleted',
            self::Restored => 'restored',
            default => '',
        };
    }
}
