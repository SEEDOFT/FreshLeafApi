<?php

declare(strict_types=1);

$host = '127.0.0.1';
$port = '8000';

// Parse arguments
foreach ($argv as $arg) {
    if (str_starts_with($arg, '--host=')) {
        $host = substr($arg, 7);
    } elseif (str_starts_with($arg, '--port=')) {
        $port = substr($arg, 7);
    }
}

echo "Starting development services on {$host}:{$port}...\n";

$env = parseEnvFile(__DIR__.'/../.env');
$llamaCppUrl = rtrim($env['LLAMA_CPP_BASE_URL'] ?? 'http://127.0.0.1:9000', '/');
$llamaCppServerCommand = trim($env['LLAMA_CPP_SERVER_COMMAND'] ?? '');

if ($llamaCppServerCommand === '' && ! isHttpEndpointReachable($llamaCppUrl)) {
    echo "Warning: llama.cpp is not reachable at {$llamaCppUrl}.\n";
    echo "Start an OpenAI-compatible llama.cpp server there, or set LLAMA_CPP_SERVER_COMMAND in .env.\n";
}

$commands = [
    "php artisan serve --host={$host} --port={$port}",
    'php artisan queue:work --queue=ai-stream,default --tries=3 --timeout=120',
    'npm run dev',
    "php artisan reverb:start --host={$host} --port=8080",
];

if ($llamaCppServerCommand !== '') {
    array_unshift($commands, $llamaCppServerCommand);
}

$concurrentCommand = 'npx concurrently -c "#93c5fd,#c4b5fd,#fdba74,#4ade80" '.
    implode(' ', array_map(static fn ($cmd) => '"'.$cmd.'"', $commands)).
    ' --names='.($llamaCppServerCommand !== '' ? 'llama,' : '').'server,queue,vite,reverb --kill-others';

passthru($concurrentCommand);

/**
 * @return array<string, string>
 */
function parseEnvFile(string $path): array
{
    if (! file_exists($path)) {
        return [];
    }

    $values = [];
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    if ($lines === false) {
        return [];
    }

    foreach ($lines as $line) {
        $line = trim($line);

        if ($line === '' || str_starts_with($line, '#') || ! str_contains($line, '=')) {
            continue;
        }

        [$key, $value] = explode('=', $line, 2);
        $values[trim($key)] = trim($value, " \t\n\r\0\x0B\"'");
    }

    return $values;
}

function isHttpEndpointReachable(string $url): bool
{
    $parts = parse_url($url);
    $host = $parts['host'] ?? null;
    $scheme = $parts['scheme'] ?? 'http';
    $port = (int) ($parts['port'] ?? ($scheme === 'https' ? 443 : 80));

    if (! is_string($host) || $host === '') {
        return false;
    }

    $connection = @fsockopen($host, $port, $errno, $errstr, 1.0);

    if ($connection === false) {
        return false;
    }

    fclose($connection);

    return true;
}
