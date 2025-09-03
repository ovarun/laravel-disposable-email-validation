<?php

namespace Ovarun\DisposableEmail\Rules;

use Illuminate\Contracts\Validation\Rule;
use Ovarun\DisposableEmail\DisposableEmailValidator;

class NotDisposableEmail implements Rule
{
    public function passes($attribute, $value)
    {
        return !(new DisposableEmailValidator())->isDisposable($value);
    }

    public function message()
    {
        return 'Disposable or blocked email addresses are not allowed.';
    }
}
