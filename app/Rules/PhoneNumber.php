<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/** A phone/contact number: numbers only, at least 10 and at most 15 digits. */
class PhoneNumber implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $value = (string) $value;

        if (!preg_match('/^[0-9]+$/', $value)) {
            $fail('The :attribute must contain numbers only (e.g. 9800000000).');
        } elseif (strlen($value) < 10) {
            $fail('The :attribute must have at least 10 digits.');
        } elseif (strlen($value) > 15) {
            $fail('The :attribute cannot have more than 15 digits.');
        }
    }
}
