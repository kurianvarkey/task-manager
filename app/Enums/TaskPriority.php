<?php

declare(strict_types=1);

namespace App\Enums;

use App\Enums\Traits\EnumCommonFunctions;

enum TaskPriority: string
{
    use EnumCommonFunctions;

    case Low = 'low';
    case Medium = 'medium';
    case High = 'high';
}
