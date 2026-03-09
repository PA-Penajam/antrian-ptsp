<?php

namespace App\Rules;

use Carbon\Carbon;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class WeekdayOnly implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) && ! $value instanceof \DateTimeInterface) {
            return;
        }

        try {
            $date = Carbon::parse($value);
        } catch (\Throwable) {
            return; // Let other rules handle invalid dates
        }

        if ($date->isWeekend()) {
            $fail('Pemesanan hanya tersedia pada hari kerja (Senin - Jumat).');
        }
    }
}
