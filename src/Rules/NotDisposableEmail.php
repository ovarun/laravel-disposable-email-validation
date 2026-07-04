<?php

namespace Ovarun\DisposableEmail\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Ovarun\DisposableEmail\DisposableEmailValidator;

class NotDisposableEmail implements ValidationRule
{
    protected DisposableEmailValidator $validator;

    public function __construct(?DisposableEmailValidator $validator = null)
    {
        $this->validator = $validator ?? app(DisposableEmailValidator::class);
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value)) {
            $fail('The :attribute must be a valid email address.');

            return;
        }

        if ($this->validator->isDisposable($value)) {
            $fail('Disposable or blocked email addresses are not allowed.');
        }
    }
}
