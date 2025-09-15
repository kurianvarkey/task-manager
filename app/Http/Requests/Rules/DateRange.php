<?php

declare(strict_types=1);

namespace App\Http\Requests\Rules;

use Carbon\Carbon;
use Closure;
use Exception;
use Illuminate\Contracts\Validation\ValidationRule;

class DateRange implements ValidationRule
{
    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (is_null($value)) {
            return;
        }

        $dates = explode(',', $value);
        $count = count($dates);

        // A valid input must have one or two parts
        if ($dates[0] === '' || $count < 1 || $count > 2) {
            $fail('The :attribute format is invalid. It must be a single date or a comma-separated date range (e.g., "YYYY-MM-DD" or "YYYY-MM-DD,YYYY-MM-DD").');

            return;
        }

        $date1 = null;
        $date2 = null;

        try {
            $date1 = Carbon::parse($dates[0]);
        } catch (Exception $e) {
            $fail('The first date in :attribute is invalid.');

            return;
        }

        // Validate the second date if it exists
        if (isset($dates[1]) && trim($dates[1]) !== '') {
            try {
                $date2 = Carbon::parse($dates[1]);
            } catch (Exception $e) {
                $fail('The second date in :attribute is invalid.');

                return;
            }
        }

        // Check if date2 is before date1
        if ($date1 && $date2 && $date2->lessThan($date1)) {
            $fail('The end date must be greater than or equal to the start date.');
        }
    }
}
