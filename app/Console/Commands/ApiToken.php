<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

/**
 * Create (or show / rotate) the API token used by OpenClaw, EasyFix and Claude.
 *
 *   php artisan api:token            Create a token if none exists, otherwise show a hint
 *   php artisan api:token --show     Print the full current token
 *   php artisan api:token --rotate   Replace the token (old one stops working)
 */
class ApiToken extends Command
{
    protected $signature = 'api:token {--show : Print the full token} {--rotate : Replace the existing token}';

    protected $description = 'Create, show or rotate the OPENCLAW_API_TOKEN used by the bot API';

    public function handle(): int
    {
        $envPath = base_path('.env');

        if (!is_file($envPath) || !is_writable($envPath)) {
            $this->error('.env not found or not writable: ' . $envPath);

            return self::FAILURE;
        }

        $env = file_get_contents($envPath);
        $current = $this->currentToken($env);

        if ($current !== '' && !$this->option('rotate')) {
            if ($this->option('show')) {
                $this->line($current);
            } else {
                $this->info('A token already exists (ends with …' . substr($current, -4) . ').');
                $this->line('Use --show to print it, or --rotate to replace it.');
            }

            return self::SUCCESS;
        }

        if ($current !== '' && $this->option('rotate')
            && !$this->confirm('Replace the token? Anything using the old one (OpenClaw, EasyFix) must be updated.', false)) {
            return self::SUCCESS;
        }

        $token = bin2hex(random_bytes(32));

        if (preg_match('/^OPENCLAW_API_TOKEN=.*$/m', $env)) {
            $env = preg_replace('/^OPENCLAW_API_TOKEN=.*$/m', 'OPENCLAW_API_TOKEN=' . $token, $env);
        } else {
            $env = rtrim($env) . PHP_EOL . 'OPENCLAW_API_TOKEN=' . $token . PHP_EOL;
        }

        file_put_contents($envPath, $env);

        $this->call('config:clear');
        if (app()->environment('production')) {
            $this->call('config:cache');
        }

        $this->info('New API token saved to .env:');
        $this->line($token);
        $this->warn('Keep it private. Send it as: Authorization: Bearer <token>');

        return self::SUCCESS;
    }

    private function currentToken(string $env): string
    {
        if (preg_match('/^OPENCLAW_API_TOKEN=(.*)$/m', $env, $m)) {
            return trim($m[1], " \t\"'");
        }

        return '';
    }
}
