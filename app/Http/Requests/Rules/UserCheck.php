<?php

declare(strict_types=1);

namespace App\Http\Requests\Rules;

use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class UserCheck implements ValidationRule
{
    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $value = (int) $value;

        if (empty($value)) {
            return;
        }

        if (User::id($value)->count('id') == 0) {
            $fail("The user with id {$value} does not exist.");
        }
    }
}
