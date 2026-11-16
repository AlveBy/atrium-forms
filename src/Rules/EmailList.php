<?php

declare(strict_types=1);

namespace Atrium\Forms\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Addresses one per line or separated by commas or semicolons.
 */
final class EmailList implements ValidationRule
{
    /**
     * @return array<int,string>
     */
    public static function split(string $value): array
    {
        $parts = preg_split('/[\s,;]+/', $value, -1, PREG_SPLIT_NO_EMPTY) ?: [];

        return array_values(array_unique($parts));
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        foreach (self::split((string) $value) as $email) {
            if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
                $fail(__('atrium-forms::forms.validation.recipients', ['email' => $email]));

                return;
            }
        }
    }
}
