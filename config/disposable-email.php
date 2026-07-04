<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Custom Blocklist
    |--------------------------------------------------------------------------
    |
    | Domains added here are blocked in addition to the bundled disposable
    | domain list (resources/domains/blocklist.php) and anything synced via
    | `php artisan disposable-email:update`.
    |
    */

    'blocklist' => [],

    /*
    |--------------------------------------------------------------------------
    | Custom Allowlist
    |--------------------------------------------------------------------------
    |
    | Domains added here are always allowed, even if they appear on the
    | bundled or synced blocklist. The allowlist always wins.
    |
    */

    'allowlist' => [],

    /*
    |--------------------------------------------------------------------------
    | Sync Settings
    |--------------------------------------------------------------------------
    |
    | Used by `php artisan disposable-email:update` to refresh the bundled
    | list from the upstream disposable-email-domains project. The synced
    | files are stored on the given disk and merged with the lists above.
    |
    */

    'sync' => [
        // List taken from : https://raw.githubusercontent.com/disposable-email-domains/disposable-email-domains/master/disposable_email_blocklist.conf
        'blocklist_url' => 'https://raw.githubusercontent.com/ovarun/laravel-disposable-email-validation/main/disposable_email_blocklist.conf',
        'allowlist_url' => 'https://raw.githubusercontent.com/ovarun/laravel-disposable-email-validation/main/allowlist.conf',
        'timeout' => 10,
        'minimum_entries' => 100,
        'disk' => 'local',
    ],

];
