<?php

namespace Ovarun\DisposableEmail\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class UpdateBlocklist extends Command
{
    protected $signature = 'disposable-email:update';
    protected $description = 'Fetch the latest disposable email blocklist from GitHub';

    public function handle()
    {
        $url = 'https://raw.githubusercontent.com/ovarun/laravel-disposable-email-validation/master/config/disposable-email.php';
        $this->info("Fetching blocklist from: $url");

        $response = Http::get($url);

        if ($response->failed()) {
            $this->error('Failed to fetch blocklist');
            return 1;
        }

        $domains = $response->json();

        $path = storage_path('app/disposable-email-blocklist.json');
        file_put_contents($path, json_encode($domains, JSON_PRETTY_PRINT));

        $this->info("Blocklist updated and stored at: $path");

        return 0;
    }
}
