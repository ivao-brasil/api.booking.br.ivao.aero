<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Auth;

class DivisionValidation implements Rule
{
    public function passes($attribute, $value)
    {
        if (!preg_match('/^[A-Z]{2}$/', $value)) {
            return false;
        }

        $userDivision = Auth::user()->division;

        return $value === $userDivision || Auth::user().admin == 2;
    }
}
