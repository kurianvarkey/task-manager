<?php

declare(strict_types=1);

namespace App\Enums;

use App\Enums\Traits\EnumCommonFunctions;

enum TaskStatus: string
{
    use EnumCommonFunctions;

    case Pending = 'pending';
    case InProgress = 'inprogress';
    case Completed = 'completed';
}
