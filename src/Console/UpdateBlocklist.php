<?php

namespace Ovarun\DisposableEmail\Console;

use Illuminate\Console\Command;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Throwable;

class UpdateBlocklist extends Command
{
    protected $signature = 'disposable-email:update';

    protected $description = 'Sync the disposable-email-domains blocklist and allowlist from upstream';

    public function handle(): int
    {
        $blocklistOk = $this->sync('blocklist', config('disposable-email.sync.blocklist_url'));
        $allowlistOk = $this->sync('allowlist', config('disposable-email.sync.allowlist_url'));

        return $blocklistOk && $allowlistOk ? self::SUCCESS : self::FAILURE;
    }

    protected function sync(string $type, string $url): bool
    {
        $this->info("Fetching {$type} from: {$url}");

        try {
            $response = Http::timeout((int) config('disposable-email.sync.timeout', 10))->get($url);
        } catch (Throwable $e) {
            $this->error("Failed to fetch {$type}: {$e->getMessage()}");

            return false;
        }

        if ($response->failed()) {
            $this->error("Failed to fetch {$type}: HTTP {$response->status()}");

            return false;
        }

        $domains = $this->parseDomains($response->body());

        $minimum = (int) config('disposable-email.sync.minimum_entries', 100);

        if (count($domains) < $minimum) {
            $this->error(
                "Refusing to update {$type}: response only contained ".count($domains).
                " domain(s), expected at least {$minimum}. The upstream response may be incomplete or invalid."
            );

            return false;
        }

        $this->writeAtomically($type, $domains);

        $this->info(ucfirst($type).' updated with '.count($domains).' domains.');

        return true;
    }

    /**
     * Parse a plain-text, one-domain-per-line list (comments starting with
     * "#" and blank lines are ignored), keeping only well-formed domains.
     *
     * @return array<int, string>
     */
    protected function parseDomains(string $body): array
    {
        $lines = preg_split('/\r\n|\r|\n/', $body) ?: [];

        $domains = array_filter(array_map(function (string $line) {
            $line = strtolower(trim($line));

            if ($line === '' || str_starts_with($line, '#')) {
                return null;
            }

            return preg_match('/^[a-z0-9]([a-z0-9-]*[a-z0-9])?(\.[a-z0-9]([a-z0-9-]*[a-z0-9])?)+$/', $line)
                ? $line
                : null;
        }, $lines));

        return array_values(array_unique($domains));
    }

    /**
     * Write the synced list to a temp file and rename it into place, so a
     * concurrent read never observes a partially written file.
     *
     * @param  array<int, string>  $domains
     */
    protected function writeAtomically(string $type, array $domains): void
    {
        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk(config('disposable-email.sync.disk', 'local'));

        $directory = 'disposable-email';
        $path = "{$directory}/{$type}.json";
        $tempPath = "{$directory}/.{$type}-".uniqid('', true).'.tmp';

        $disk->makeDirectory($directory);
        $disk->put($tempPath, json_encode($domains, JSON_PRETTY_PRINT));

        rename($disk->path($tempPath), $disk->path($path));
    }
}
