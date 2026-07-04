<?php

namespace Ovarun\DisposableEmail\Tests\Console;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Ovarun\DisposableEmail\DisposableEmailValidator;
use Ovarun\DisposableEmail\Tests\TestCase;

class UpdateBlocklistTest extends TestCase
{
    protected function defineEnvironment($app): void
    {
        parent::defineEnvironment($app);

        $app['config']->set('disposable-email.sync.minimum_entries', 2);
    }

    public function test_it_syncs_and_stores_the_blocklist_and_allowlist(): void
    {
        Http::fake([
            config('disposable-email.sync.blocklist_url') => Http::response("# comment\nsynced-block-one.test\nsynced-block-two.test\n"),
            config('disposable-email.sync.allowlist_url') => Http::response("synced-allow-one.test\nsynced-allow-two.test\n"),
        ]);

        $this->artisan('disposable-email:update')->assertExitCode(0);

        $this->assertTrue(Storage::disk('local')->exists('disposable-email/blocklist.json'));
        $this->assertTrue(Storage::disk('local')->exists('disposable-email/allowlist.json'));

        $validator = new DisposableEmailValidator();

        $this->assertTrue($validator->isDisposable('user@synced-block-one.test'));
        $this->assertFalse($validator->isDisposable('user@synced-allow-one.test'));
    }

    public function test_it_refuses_to_overwrite_with_a_suspiciously_small_response(): void
    {
        config()->set('disposable-email.sync.minimum_entries', 100);

        Http::fake([
            config('disposable-email.sync.blocklist_url') => Http::response('just-one-domain.test'),
            config('disposable-email.sync.allowlist_url') => Http::response('just-one-domain.test'),
        ]);

        $this->artisan('disposable-email:update')->assertExitCode(1);

        $this->assertFalse(Storage::disk('local')->exists('disposable-email/blocklist.json'));
    }

    public function test_it_fails_cleanly_when_the_request_fails(): void
    {
        Http::fake([
            config('disposable-email.sync.blocklist_url') => Http::response('', 500),
            config('disposable-email.sync.allowlist_url') => Http::response('', 500),
        ]);

        $this->artisan('disposable-email:update')->assertExitCode(1);
    }
}
