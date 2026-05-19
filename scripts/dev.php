<?php

declare(strict_types=1);

// ---------------------------------------------------------------------------
// Platform detection
// ---------------------------------------------------------------------------
const IS_WINDOWS = PHP_OS_FAMILY === 'Windows';
const IS_MACOS = PHP_OS_FAMILY === 'Darwin';
const IS_LINUX = PHP_OS_FAMILY === 'Linux';

// ---------------------------------------------------------------------------
// CLI arguments
// ---------------------------------------------------------------------------
$host = '127.0.0.1';
$port = '8000';

foreach ($argv as $arg) {
    if (\str_starts_with($arg, '--host=')) {
        $host = \substr($arg, 7);
    } elseif (\str_starts_with($arg, '--port=')) {
        $port = \substr($arg, 7);
    }
}

// ---------------------------------------------------------------------------
// ANSI colour support
// ---------------------------------------------------------------------------
/**
 * Windows: enabled only when running inside Windows Terminal, VS Code, or
 * when ANSICON / ConEmu are present, or when the VT-processing flag is set.
 * We probe the environment rather than call kernel32 so no FFI is required.
 */
function ansiSupported(): bool
{
    if (IS_WINDOWS) {
        // Common terminals that support ANSI on Windows
        return \getenv('WT_SESSION') !== false       // Windows Terminal
            || \getenv('TERM_PROGRAM') !== false     // VS Code, Hyper …
            || \getenv('ANSICON') !== false          // ANSICON shim
            || \getenv('ConEmuANSI') === 'ON';       // ConEmu / cmder
    }

    // macOS / Linux: check $TERM and isatty
    if (\function_exists('posix_isatty') && ! \posix_isatty(STDOUT)) {
        return false; // piped / redirected output
    }

    $term = (string) \getenv('TERM');

    return $term !== '' && $term !== 'dumb';
}

$useAnsi = ansiSupported();

$reset = $useAnsi ? "\033[0m" : '';
$bold = $useAnsi ? "\033[1m" : '';
$blue = $useAnsi ? "\033[38;2;147;197;253m" : '';
$purple = $useAnsi ? "\033[38;2;196;181;253m" : '';
$orange = $useAnsi ? "\033[38;2;253;186;116m" : '';
$green = $useAnsi ? "\033[38;2;74;222;128m" : '';

echo "\n";
echo "{$bold}Starting development services...{$reset}\n";
echo "\n";
echo "  {$blue}⬡  [server]{$reset}  →  http://{$host}:{$port}\n";
echo "  {$purple}⬡  [queue]{$reset}   →  ai-stream, default\n";
echo "  {$orange}⬡  [vite]{$reset}    →  npm run dev\n";
echo "  {$green}⬡  [reverb]{$reset}  →  ws://{$host}:8080\n";
echo "\n";

// ---------------------------------------------------------------------------
// Helpers
// ---------------------------------------------------------------------------
function colorLine(string $color, string $name, string $line, bool $useAnsi): void
{
    $reset = $useAnsi ? "\033[0m" : '';
    echo "{$color}[{$name}] {$line}{$reset}".PHP_EOL;
}

/**
 * Resolve the npm executable name.
 * On Windows the PATH entry is `npm.cmd`; everywhere else it is `npm`.
 */
function npmBin(): string
{
    if (! IS_WINDOWS) {
        return 'npm';
    }

    // Search PATH explicitly so proc_open can find it when given an array.
    foreach (\explode(PATH_SEPARATOR, (string) \getenv('PATH')) as $dir) {
        $candidate = \rtrim($dir, '/\\').DIRECTORY_SEPARATOR.'npm.cmd';
        if (\is_file($candidate)) {
            return $candidate;
        }
    }

    return 'npm.cmd'; // fallback – let the shell resolve it
}

/**
 * Start a child process.
 *
 * On Windows, proc_open() cannot use `stream_select()` on pipe streams
 * (they are not sockets).  We therefore open the child's stdout/stderr as
 * *named pipes* (temp files) on Windows, and poll them with fgets() instead.
 *
 * On POSIX (Linux/macOS) we use anonymous pipes + stream_select() as before.
 *
 * Returns a process descriptor array.
 */
function startProcess(array $command, string $cwd): array
{
    if (IS_WINDOWS) {
        // Named-pipe trick: redirect to temp files, read with fgets polling.
        $stdoutFile = \tempnam(\sys_get_temp_dir(), 'dev_stdout_');
        $stderrFile = \tempnam(\sys_get_temp_dir(), 'dev_stderr_');

        $descriptors = [
            0 => ['pipe', 'r'],
            1 => ['file', $stdoutFile, 'w'],
            2 => ['file', $stderrFile, 'w'],
        ];

        // proc_open on Windows needs a string command (not an array) when the
        // executable is a .cmd batch file; for .exe files an array is fine.
        // To stay safe we always convert to a quoted string on Windows.
        $commandStr = windowsCommandString($command);
        $process = \proc_open($commandStr, $descriptors, $pipes, $cwd);

        if (! \is_resource($process)) {
            throw new RuntimeException("Failed to start process: {$commandStr}");
        }

        \fclose($pipes[0]);

        // Open the temp files for reading (they will grow as the child writes).
        $stdoutHandle = \fopen($stdoutFile, 'rb');
        $stderrHandle = \fopen($stderrFile, 'rb');

        if ($stdoutHandle) {
            \stream_set_blocking($stdoutHandle, false);
        }
        if ($stderrHandle) {
            \stream_set_blocking($stderrHandle, false);
        }

        return [
            'process' => $process,
            'pipes' => $pipes,
            'stdout' => '',
            'stderr' => '',
            'running' => true,
            'win_stdout' => $stdoutHandle,
            'win_stderr' => $stderrHandle,
            'win_files' => [$stdoutFile, $stderrFile],
        ];
    }

    // ---- POSIX path (Linux / macOS) ----------------------------------------
    $descriptors = [
        0 => ['pipe', 'r'],
        1 => ['pipe', 'w'],
        2 => ['pipe', 'w'],
    ];

    $process = \proc_open($command, $descriptors, $pipes, $cwd);

    if (! \is_resource($process)) {
        throw new RuntimeException('Failed to start process: '.\implode(' ', $command));
    }

    \fclose($pipes[0]);
    \stream_set_blocking($pipes[1], false);
    \stream_set_blocking($pipes[2], false);

    return [
        'process' => $process,
        'pipes' => $pipes,
        'stdout' => '',
        'stderr' => '',
        'running' => true,
    ];
}

/**
 * Convert a command array to a properly quoted Windows command string.
 * Rules: each token is wrapped in double quotes; embedded double quotes are
 * escaped with backslash.
 */
function windowsCommandString(array $command): string
{
    $parts = [];
    foreach ($command as $token) {
        // Escape existing double-quotes inside the token.
        $token = \str_replace('"', '\\"', $token);
        $parts[] = '"'.$token.'"';
    }

    return \implode(' ', $parts);
}

/**
 * Drain any available output from a process on Windows (file-polling mode).
 * Returns lines that were read.
 */
function drainWindowsStreams(array &$job): array
{
    $lines = [];

    foreach (['win_stdout' => 'stdout', 'win_stderr' => 'stderr'] as $handle => $bufKey) {
        if (! \is_resource($job[$handle] ?? null)) {
            continue;
        }

        while (($chunk = \fread($job[$handle], 8192)) !== false && $chunk !== '') {
            $job[$bufKey] .= $chunk;
        }

        while (($pos = \strpos($job[$bufKey], "\n")) !== false) {
            $lines[] = \substr($job[$bufKey], 0, $pos);
            $job[$bufKey] = \substr($job[$bufKey], $pos + 1);
        }
    }

    return $lines;
}

/**
 * Close and clean up Windows temp file handles / files.
 */
function cleanupWindows(array $job): void
{
    foreach (['win_stdout', 'win_stderr'] as $handle) {
        if (\is_resource($job[$handle] ?? null)) {
            \fclose($job[$handle]);
        }
    }

    foreach ($job['win_files'] ?? [] as $file) {
        if (\file_exists($file)) {
            @\unlink($file);
        }
    }
}

// ---------------------------------------------------------------------------
// Signal handling (graceful shutdown on Ctrl-C)
// Works on POSIX; on Windows pcntl is usually unavailable but we try anyway.
// ---------------------------------------------------------------------------
if (\function_exists('pcntl_async_signals')) {
    \pcntl_async_signals(true);
}

$shutdownRequested = false;

if (\function_exists('pcntl_signal')) {
    \pcntl_signal(SIGINT, function () use (&$shutdownRequested) {
        $shutdownRequested = true;
    });
    \pcntl_signal(SIGTERM, function () use (&$shutdownRequested) {
        $shutdownRequested = true;
    });
}

// ---------------------------------------------------------------------------
// Job definitions
// ---------------------------------------------------------------------------
$cwd = \getcwd() ?: __DIR__;

$jobs = [
    [
        'name' => 'server',
        'color' => $blue,
        'command' => [PHP_BINARY, 'artisan', 'serve', "--host={$host}", "--port={$port}"],
    ],
    [
        'name' => 'queue',
        'color' => $purple,
        'command' => [PHP_BINARY, 'artisan', 'queue:work', '--queue=ai-stream,default', '--tries=3', '--timeout=120'],
    ],
    [
        'name' => 'vite',
        'color' => $orange,
        'command' => [npmBin(), 'run', 'dev', '--', "--host={$host}"],
    ],
    [
        'name' => 'reverb',
        'color' => $green,
        'command' => [PHP_BINARY, 'artisan', 'reverb:start', "--host={$host}", '--port=8080'],
    ],
];

// ---------------------------------------------------------------------------
// Start all jobs
// ---------------------------------------------------------------------------
$running = [];
foreach ($jobs as $job) {
    $running[] = $job + startProcess($job['command'], $cwd);
}

// ---------------------------------------------------------------------------
// Main event loop
// ---------------------------------------------------------------------------
while (! empty($running)) {

    // ---- Graceful shutdown --------------------------------------------------
    if ($shutdownRequested) {
        echo "\n{$bold}Shutting down…{$reset}\n";
        foreach ($running as $job) {
            if (\is_resource($job['process'])) {
                // Send SIGTERM on POSIX; on Windows proc_terminate() sends
                // WM_CLOSE which is the closest equivalent.
                \proc_terminate($job['process']);
            }
        }

        // Give children a moment to exit, then force-close.
        \usleep(500_000);

        foreach ($running as $job) {
            foreach ($job['pipes'] ?? [] as $pipe) {
                if (\is_resource($pipe)) {
                    \fclose($pipe);
                }
            }

            if (IS_WINDOWS) {
                cleanupWindows($job);
            }

            if (\is_resource($job['process'])) {
                \proc_close($job['process']);
            }
        }

        exit(0);
    }

    // ---- Read output --------------------------------------------------------
    if (IS_WINDOWS) {
        // Polling: sleep a short while, then drain temp files.
        \usleep(100_000); // 100 ms

        foreach ($running as $index => &$job) {
            foreach (drainWindowsStreams($job) as $line) {
                if (\trim($line) !== '') {
                    colorLine($job['color'], $job['name'], \rtrim($line, "\r"), $useAnsi);
                }
            }
        }
        unset($job);
    } else {
        // POSIX: use stream_select() for efficient multiplexing.
        $read = [];
        $map = [];

        foreach ($running as $index => $job) {
            if (\is_resource($job['pipes'][1])) {
                $read[] = $job['pipes'][1];
                $map[(int) $job['pipes'][1]] = [$index, 'stdout'];
            }
            if (\is_resource($job['pipes'][2])) {
                $read[] = $job['pipes'][2];
                $map[(int) $job['pipes'][2]] = [$index, 'stderr'];
            }
        }

        $write = null;
        $except = null;

        if ($read && \stream_select($read, $write, $except, 0, 200_000) !== false) {
            foreach ($read as $stream) {
                $key = (int) $stream;
                if (! isset($map[$key])) {
                    continue;
                }

                [$index, $type] = $map[$key];
                $chunk = \stream_get_contents($stream);

                if ($chunk === '' || $chunk === false) {
                    continue;
                }

                $running[$index][$type] .= $chunk;

                while (($pos = \strpos($running[$index][$type], "\n")) !== false) {
                    $line = \substr($running[$index][$type], 0, $pos);
                    $running[$index][$type] = \substr($running[$index][$type], $pos + 1);

                    if (\trim($line) !== '') {
                        colorLine(
                            $running[$index]['color'],
                            $running[$index]['name'],
                            \rtrim($line, "\r"),
                            $useAnsi
                        );
                    }
                }
            }
        }
    }

    // ---- Reap finished processes --------------------------------------------
    foreach ($running as $index => $job) {
        $status = \proc_get_status($job['process']);

        if ($status['running'] === true) {
            continue;
        }

        // Flush any remaining buffered output.
        foreach (['stdout', 'stderr'] as $type) {
            $buffer = $running[$index][$type] ?? '';
            if (\trim($buffer) !== '') {
                foreach (\preg_split("/\r\n|\n|\r/", $buffer) as $line) {
                    if ($line !== '') {
                        colorLine($job['color'], $job['name'], $line, $useAnsi);
                    }
                }
            }
        }

        // Close POSIX pipes.
        foreach ($job['pipes'] as $pipe) {
            if (\is_resource($pipe)) {
                \fclose($pipe);
            }
        }

        // Clean up Windows temp files.
        if (IS_WINDOWS) {
            cleanupWindows($job);
        }

        \proc_close($job['process']);
        unset($running[$index]);
    }

    $running = \array_values($running);
}
