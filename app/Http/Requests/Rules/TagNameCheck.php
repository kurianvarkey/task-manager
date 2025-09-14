<?php

declare(strict_types=1);

namespace App\Http\Requests\Rules;

use App\Models\Tag;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class TagNameCheck implements ValidationRule
{
    /**
     * The ID to ignore.
     */
    private ?int $ignoreId;

    public function __construct($ignoreId = null)
    {
        $this->ignoreId = (int) $ignoreId;
    }

    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $query = Tag::name($value);
        if ($this->ignoreId > 0) {
            $query->where('id', '!=', $this->ignoreId);
        }

        if ($query->count('id')) {
            $fail("The :attribute {$value} has already been taken.");
        }
    }
}
