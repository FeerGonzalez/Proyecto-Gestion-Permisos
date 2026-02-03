<?php

namespace App\Rules;

use Illuminate\Validation\Rules\Password;

class PasswordRules
{
    /**
     * Get the standard password validation rules for the application.
     * Ensures consistent password requirements across all forms.
     *
     * @return array
     */
    public static function defaults(): array
    {
        return [
            'required',
            'confirmed',
            Password::min(8)
                ->letters()
                ->mixedCase()
                ->numbers(),
        ];
    }

    /**
     * Get password rules without confirmation requirement.
     * Used for admin user creation where confirmation may not be needed.
     *
     * @return array
     */
    public static function withoutConfirmation(): array
    {
        return [
            'required',
            Password::min(8)
                ->letters()
                ->mixedCase()
                ->numbers(),
        ];
    }
}
