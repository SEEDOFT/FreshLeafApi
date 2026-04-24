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

$commands = [
    "php artisan serve --host={$host} --port={$port}",
    'php artisan queue:work --queue=ai-stream,default --tries=3 --timeout=120',
    'npm run dev',
    "php artisan reverb:start --host={$host} --port=8080",
];

$concurrentCommand = 'npx concurrently -c "#93c5fd,#c4b5fd,#fdba74,#4ade80" '.
    implode(' ', array_map(static fn ($cmd) => '"'.$cmd.'"', $commands)).
    ' --names=server,queue,vite,reverb --kill-others';

passthru($concurrentCommand);
