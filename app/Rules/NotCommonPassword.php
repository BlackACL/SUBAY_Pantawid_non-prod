<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class NotCommonPassword implements ValidationRule
{
    /**
     * List of common/banned passwords
     */
    protected $bannedPasswords = [
        'password',
        'password123',
        'password1234',
        'password12345',
        '123456',
        '12345678',
        '123456789',
        'qwerty',
        'qwerty123',
        'abc123',
        'password!',
        'admin',
        'admin123',
        'welcome',
        'welcome123',
        'letmein',
        'monkey',
        '1234567890',
        'password1',
        'iloveyou',
        'sunshine',
        'princess',
        'dragon',
        'master',
        'trustno1',
        'football',
        'baseball',
        'superman',
        'batman',
        'Pantawid123',
        'Pantawid@123',
        'DSWD123',
        'DSWD@123',
    ];

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $lowercasePassword = strtolower($value);
        
        foreach ($this->bannedPasswords as $banned) {
            if (strtolower($banned) === $lowercasePassword) {
                $fail('This password is too common and not allowed. Please choose a more secure password.');
                return;
            }
        }
    }
}
