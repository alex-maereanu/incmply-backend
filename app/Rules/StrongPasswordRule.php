<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class StrongPasswordRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $match = preg_match('/^(?=.*[A-Z])(?=.*[\W_]).*$/', $value);
        if ( ! $match) {
            $fail(__('Must include: uppercase letter and one special character.'));
        }
    }
}
