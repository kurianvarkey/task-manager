<?php

declare(strict_types=1);

namespace App\Enums;

use App\Enums\Traits\EnumCommonFunctions;

enum Role: string
{
    use EnumCommonFunctions;

    case Admin = 'admin';
    case User = 'user';
}
