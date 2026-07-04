<?php

namespace Ovarun\DisposableEmail\Tests\Rules;

use Illuminate\Support\Facades\Validator;
use Ovarun\DisposableEmail\Rules\NotDisposableEmail;
use Ovarun\DisposableEmail\Tests\TestCase;

class NotDisposableEmailTest extends TestCase
{
    public function test_it_passes_for_a_normal_email(): void
    {
        $validator = Validator::make(
            ['email' => 'user@example.com'],
            ['email' => ['required', new NotDisposableEmail()]],
        );

        $this->assertTrue($validator->passes());
    }

    public function test_it_fails_for_a_disposable_email(): void
    {
        $validator = Validator::make(
            ['email' => 'user@mailinator.com'],
            ['email' => ['required', new NotDisposableEmail()]],
        );

        $this->assertTrue($validator->fails());
        $this->assertSame(
            'Disposable or blocked email addresses are not allowed.',
            $validator->errors()->first('email'),
        );
    }

    public function test_it_fails_gracefully_for_a_non_string_value(): void
    {
        $validator = Validator::make(
            ['email' => ['not', 'a', 'string']],
            ['email' => [new NotDisposableEmail()]],
        );

        $this->assertTrue($validator->fails());
    }
}
