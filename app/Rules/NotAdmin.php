<?php

namespace App\Rules;

use App\Models\User\User;
use Illuminate\Contracts\Validation\Rule;

class NotAdmin implements Rule
{
    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $user = User::find($value);

        // Assuming 'is_admin' is a boolean field in your users table
        return $user && !$user->isAdmin;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Invalid User';
    }
}

