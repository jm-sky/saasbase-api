<?php

namespace App\Domain\Invoice\Rules;

use Illuminate\Contracts\Validation\ValidationRule;

class ValidNumberingFormatRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, \Closure $fail): void
    {
        // Count occurrences of NN, NNN, NNNN
        $matches = [];
        preg_match_all('/N{2,4}/', $value, $matches);
        $found = $matches[0] ?? [];

        // Only allow exactly one, and it must be NN, NNN, or NNNN
        if (1 !== count($found) || !in_array($found[0], ['NN', 'NNN', 'NNNN'], true)) {
            $fail(__('The :attribute must contain exactly one of: NN, NNN, or NNNN.'));
        }
    }
}
