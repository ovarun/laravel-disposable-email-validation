<?php

namespace Ovarun\DisposableEmail;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DisposableEmailValidator
{
    protected array $blocklist;

    protected array $allowlist;

    public function __construct()
    {
        $this->blocklist = $this->buildSet(
            require __DIR__.'/../resources/domains/blocklist.php',
            $this->loadSynced('blocklist'),
            config('disposable-email.blocklist', []),
        );

        $this->allowlist = $this->buildSet(
            require __DIR__.'/../resources/domains/allowlist.php',
            $this->loadSynced('allowlist'),
            config('disposable-email.allowlist', []),
        );
    }

    public function isDisposable(string $email): bool
    {
        $domain = $this->extractDomain($email);

        if ($domain === null) {
            return false;
        }

        if (isset($this->allowlist[$domain])) {
            return false; // allowlist always wins
        }

        return isset($this->blocklist[$domain]);
    }

    protected function extractDomain(string $email): ?string
    {
        $email = trim($email);

        if (! str_contains($email, '@')) {
            return null;
        }

        $domain = Str::lower(Str::afterLast($email, '@'));

        return $domain === '' ? null : $domain;
    }

    /**
     * Merge one or more domain lists into a flipped (O(1) lookup) set.
     */
    protected function buildSet(array ...$sources): array
    {
        $domains = array_map(
            fn (string $domain) => Str::lower(trim($domain)),
            array_merge(...$sources),
        );

        return array_flip(array_filter($domains, fn (string $domain) => $domain !== ''));
    }

    protected function loadSynced(string $type): array
    {
        $disk = Storage::disk(config('disposable-email.sync.disk', 'local'));
        $path = "disposable-email/{$type}.json";

        if (! $disk->exists($path)) {
            return [];
        }

        $decoded = json_decode($disk->get($path), true);

        return is_array($decoded) ? $decoded : [];
    }
}
