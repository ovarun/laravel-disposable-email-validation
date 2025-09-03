<?php

namespace Ovarun\DisposableEmail;

class DisposableEmailValidator
{
    protected array $blocklist;
    protected array $allowlist;

    public function __construct()
    {
        // Load synced blocklist (if exists)
        $syncedBlocklist = [];
        $path = storage_path('app/disposable-email-blocklist.json');
        if (file_exists($path)) {
            $syncedBlocklist = json_decode(file_get_contents($path), true) ?? [];
        }

        // Merge synced + config blocklist
        $this->blocklist = array_unique(array_merge(
            $syncedBlocklist,
            config('disposable-email.blocklist', [])
        ));

        $this->allowlist = config('disposable-email.allowlist', []);
    }

    public function isDisposable(string $email): bool
    {
        $domain = strtolower(substr(strrchr($email, "@"), 1));

        if (in_array($domain, $this->allowlist, true)) {
            return false; // allowlist always wins
        }

        return in_array($domain, $this->blocklist, true);
    }
}
