<?php

namespace App\Domain\Common\Rules;

use App\Domain\Common\Enums\AdvancedFilterOperator;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidAdvancedFilterRule implements ValidationRule
{
    protected string $type;

    protected array $allowedOperators;

    public function __construct(string $type = 'string', array $allowedOperators = [])
    {
        $this->type             = $type;
        $this->allowedOperators = $allowedOperators ?: AdvancedFilterOperator::values();
    }

    public function validate(string $attribute, mixed $value, \Closure $fail): void
    {
        if (is_array($value)) {
            foreach ($value as $operator => $val) {
                $normalizedOperator = strtolower($operator);
                $allowedOperators   = array_map('strtolower', $this->allowedOperators);

                if (!in_array($normalizedOperator, $allowedOperators, true)) {
                    $fail("The :attribute filter contains an invalid operator: {$operator}.");

                    return;
                }

                if (!$this->validateValueByType($val, $this->type, $normalizedOperator, $fail)) {
                    return;
                }
            }
        } else {
            if (!$this->validateValueByType($value, $this->type, strtolower(AdvancedFilterOperator::Equals->value), $fail)) {
                return;
            }
        }
    }

    protected function validateValueByType($value, string $type, string $operator, \Closure $fail): bool
    {
        // Null checks
        if (in_array($operator, [AdvancedFilterOperator::IsNull->value, AdvancedFilterOperator::IsNotNull->value, AdvancedFilterOperator::IsNullish->value], true)) {
            return true;
        }

        // Set membership
        if (in_array($operator, [AdvancedFilterOperator::In->value, AdvancedFilterOperator::NotIn->value, AdvancedFilterOperator::NotInAlt->value], true)) {
            if (!is_array($value) && !is_string($value)) {
                $fail('The :attribute filter for operator ' . $operator . ' must be an array or comma-separated string.');

                return false;
            }

            return true;
        }

        // Between
        if ($operator === AdvancedFilterOperator::Between->value) {
            if (is_array($value)) {
                if (2 !== count($value)) {
                    $fail('The :attribute filter for between must have exactly two values.');

                    return false;
                }

                foreach ($value as $v) {
                    if (!$this->validateSingleType($v, $type, $fail)) {
                        return false;
                    }
                }
            } elseif (is_string($value)) {
                $parts = explode(',', $value);

                if (2 !== count($parts)) {
                    $fail('The :attribute filter for between must have exactly two comma-separated values.');

                    return false;
                }

                foreach ($parts as $v) {
                    if (!$this->validateSingleType($v, $type, $fail)) {
                        return false;
                    }
                }
            } else {
                $fail('The :attribute filter for between must be an array or comma-separated string.');

                return false;
            }

            return true;
        }

        // String operators
        if (in_array($operator, [AdvancedFilterOperator::Like->value, AdvancedFilterOperator::NotLike->value, AdvancedFilterOperator::NotLikeAlt->value, AdvancedFilterOperator::StartsWith->value, AdvancedFilterOperator::EndsWith->value, AdvancedFilterOperator::Regex->value], true)) {
            if ('string' !== $type) {
                $fail('The :attribute filter for operator ' . $operator . ' must be a string.');

                return false;
            }

            if (!is_string($value)) {
                $fail('The :attribute filter for operator ' . $operator . ' must be a string value.');

                return false;
            }

            return true;
        }

        // Comparison
        return $this->validateSingleType($value, $type, $fail);
    }

    protected function validateSingleType($value, string $type, \Closure $fail): bool
    {
        return match ($type) {
            'string' => is_string($value),
            'int', 'integer' => false !== filter_var($value, FILTER_VALIDATE_INT),
            'float', 'double' => false !== filter_var($value, FILTER_VALIDATE_FLOAT),
            'bool', 'boolean' => is_bool($value) || 0 === $value || 1 === $value || '0' === $value || '1' === $value || true === $value || false === $value,
            'date'  => $this->validateDate($value),
            default => true,
        };
    }

    protected function validateDate($value): bool
    {
        if ($value instanceof \DateTimeInterface) {
            return true;
        }

        if (is_string($value)) {
            return false !== strtotime($value);
        }

        return false;
    }
}
