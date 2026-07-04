<?php

namespace Ovarun\DisposableEmail\Tests;

use Illuminate\Support\Facades\Storage;
use Ovarun\DisposableEmail\DisposableEmailValidator;

class DisposableEmailValidatorTest extends TestCase
{
    public function test_it_detects_a_bundled_disposable_domain(): void
    {
        $validator = new DisposableEmailValidator();

        $this->assertTrue($validator->isDisposable('user@mailinator.com'));
    }

    public function test_it_allows_a_normal_domain(): void
    {
        $validator = new DisposableEmailValidator();

        $this->assertFalse($validator->isDisposable('user@example.com'));
    }

    public function test_domain_matching_is_case_insensitive(): void
    {
        $validator = new DisposableEmailValidator();

        $this->assertTrue($validator->isDisposable('user@MAILINATOR.COM'));
    }

    public function test_custom_config_blocklist_is_honored(): void
    {
        config()->set('disposable-email.blocklist', ['blocked-by-config.test']);

        $validator = new DisposableEmailValidator();

        $this->assertTrue($validator->isDisposable('user@blocked-by-config.test'));
    }

    public function test_allowlist_always_wins_over_blocklist(): void
    {
        config()->set('disposable-email.blocklist', ['tricky.test']);
        config()->set('disposable-email.allowlist', ['tricky.test']);

        $validator = new DisposableEmailValidator();

        $this->assertFalse($validator->isDisposable('user@tricky.test'));
    }

    public function test_synced_blocklist_file_is_merged_in(): void
    {
        Storage::disk('local')->put(
            'disposable-email/blocklist.json',
            json_encode(['synced-domain.test'])
        );

        $validator = new DisposableEmailValidator();

        $this->assertTrue($validator->isDisposable('user@synced-domain.test'));
    }

    public function test_an_email_without_an_at_sign_is_not_disposable(): void
    {
        $validator = new DisposableEmailValidator();

        $this->assertFalse($validator->isDisposable('not-an-email'));
    }
}
