<?php

declare(strict_types=1);

$host = '127.0.0.1';
$port = '8000';

// Parse arguments
foreach ($argv as $arg) {
    if (\str_starts_with($arg, '--host=')) {
        $host = \substr($arg, 7);
    } elseif (\str_starts_with($arg, '--port=')) {
        $port = \substr($arg, 7);
    }
}

// ANSI colors for the startup message
$reset = "\033[0m";
$bold = "\033[1m";
$blue = "\033[38;2;147;197;253m"; // #93c5fd — server
$purple = "\033[38;2;196;181;253m"; // #c4b5fd — queue
$orange = "\033[38;2;253;186;116m"; // #fdba74 — vite
$green = "\033[38;2;74;222;128m";  // #4ade80 — reverb

echo "\n";
echo "{$bold}Starting development services...{$reset}\n";
echo "\n";
echo "  {$blue}⬡  [server]{$reset}  →  http://{$host}:{$port}\n";
echo "  {$purple}⬡  [queue]{$reset}   →  ai-stream, default\n";
echo "  {$orange}⬡  [vite]{$reset}    →  npm run dev\n";
echo "  {$green}⬡  [reverb]{$reset}  →  ws://{$host}:8080\n";
echo "\n";

/** @var string[] $commands */
$commands = [
    "php artisan serve --host={$host} --port={$port}",
    'php artisan queue:work --queue=ai-stream,default --tries=3 --timeout=120',
    'npm run dev',
    "php artisan reverb:start --host={$host} --port=8080",
];

$names = 'server,queue,vite,reverb';
$colors = '#93c5fd,#c4b5fd,#fdba74,#4ade80';

$quoted = \implode(' ', \array_map(
    static fn (string $cmd) => '"'.$cmd.'"',
    $commands
));

$concurrentCommand = 'npx concurrently'
    .' --colors'
    ." -c \"{$colors}\""
    ." --names \"{$names}\""
    .' --prefix "[{name}]"'
    .' --kill-others'
    .' --kill-others-on-fail'
    ." {$quoted}";

\passthru($concurrentCommand);
