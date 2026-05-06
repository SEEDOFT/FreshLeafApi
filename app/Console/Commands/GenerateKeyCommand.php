<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

#[Signature('make:key {name? : The name of the environment variable (e.g. ADMIN_REGISTRATION_KEY)} {--length=32 : The byte length of the key (hex output will be double)} {--force : Overwrite existing key in .env}')]
#[Description('Generate a secure random key and optionally save it to the .env file')]
class GenerateKeyCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $name = $this->argument('name');
        $length = (int) $this->option('length');
        $length = max(1, $length);

        // Generate secure hex key
        $key = bin2hex(random_bytes($length));

        if (! $name) {
            $this->info('Generated Secure Key:');
            $this->line($key);

            return self::SUCCESS;
        }

        if (! $this->writeToEnv($name, $key)) {
            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    /**
     * Write the key to the .env file.
     */
    private function writeToEnv(string $name, string $key): bool
    {
        $path = base_path('.env');

        if (! file_exists($path)) {
            $this->error('.env file not found.');

            return false;
        }

        $content = file_get_contents($path);
        if ($content === false) {
            $this->error('Failed to read .env file.');

            return false;
        }

        $keyExists = Str::contains($content, "{$name}=");

        if ($keyExists && ! $this->option('force')) {
            $this->warn("The key [{$name}] already exists in your .env file.");
            if (! $this->confirm('Do you want to overwrite it?', false)) {
                return false;
            }
        }

        if ($keyExists) {
            $content = preg_replace(
                "/^{$name}=.*/m",
                "{$name}={$key}",
                $content
            ) ?? $content;
        } else {
            $content .= "\n{$name}={$key}\n";
        }

        file_put_contents($path, $content);

        $this->info("Successfully generated and saved key for [{$name}].");
        $this->line("Key: {$key}");

        return true;
    }
}
