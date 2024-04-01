<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class LowerCase implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if($value !== strtolower($value))
        {
            $fail("validation.custom.lowercase")->translate([
                "attribute" => $attribute,
                "value" => $value
            ]);
        }
    }
}
