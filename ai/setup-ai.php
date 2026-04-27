<?php

declare(strict_types=1);

/**
 * Setup AI script for FreshLeaf project.
 * Downloads a llama.cpp server runtime and the Phi-3 GGUF model.
 */
$os = PHP_OS_FAMILY;
$arch = \php_uname('m');

echo "Detected OS: {$os}, Architecture: {$arch}\n";

$binDir = __DIR__.'/bin';
$modelDir = __DIR__.'/models';

ensureDirectory($binDir);
ensureDirectory($modelDir);

$llamaVersion = 'b8893';
$baseUrl = "https://github.com/ggml-org/llama.cpp/releases/download/{$llamaVersion}/";
$engineBinary = $os === 'Windows' ? 'llama-server.exe' : 'llama-server';
$enginePath = $binDir.'/'.$engineBinary;

[$archiveFile, $isZip] = match ($os) {
    'Windows' => ["llama-{$llamaVersion}-bin-win-cpu-x64.zip", true],
    'Darwin' => [\str_contains(\strtolower($arch), 'arm')
        ? "llama-{$llamaVersion}-bin-macos-arm64.tar.gz"
        : "llama-{$llamaVersion}-bin-macos-x64.tar.gz", false],
    default => ["llama-{$llamaVersion}-bin-ubuntu-x64.tar.gz", false],
};

$downloadUrl = $baseUrl.$archiveFile;
$targetPath = $binDir.'/'.$archiveFile;

if (isEngineUsable($enginePath)) {
    echo "AI engine already exists and is runnable. Skipping engine download.\n";
} else {
    if (\file_exists($enginePath)) {
        echo "Existing AI engine is not runnable. Reinstalling engine...\n";
    }

    echo "Downloading llama.cpp engine from {$downloadUrl}...\n";
    if (! downloadFile($downloadUrl, $targetPath)) {
        exit("Error: Failed to download llama.cpp engine. Please check your internet connection.\n");
    }

    cleanDirectory($binDir, ['.gitkeep', $archiveFile]);

    echo "Extracting engine...\n";
    $extractDir = $binDir.'/extract-'.\uniqid('', true);
    ensureDirectory($extractDir);

    $extracted = $isZip
        ? extractZip($targetPath, $extractDir)
        : extractTarGz($targetPath, $extractDir);

    if (! $extracted) {
        cleanDirectory($extractDir);
        @\rmdir($extractDir);
        exit("Error: Failed to extract llama.cpp engine archive.\n");
    }

    flattenExtractedFiles($extractDir, $binDir);
    cleanDirectory($extractDir);
    @\rmdir($extractDir);
    @\unlink($targetPath);

    if (! isEngineUsable($enginePath)) {
        exit("Error: llama-server was installed, but it cannot run on this machine. Check missing DLL/runtime dependencies.\n");
    }

    echo "Engine installed successfully.\n";
}

$existingModel = findInstalledModel($modelDir);
$modelUrl = 'https://huggingface.co/microsoft/Phi-3-mini-4k-instruct-gguf/resolve/main/Phi-3-mini-4k-instruct-q4.gguf';
$modelPath = $modelDir.'/Phi-3-mini-4k-instruct-q4.gguf';

if ($existingModel !== null) {
    echo "Model already exists and seems valid: {$existingModel}\n";
} else {
    echo "Downloading Phi-3 model (~2.4GB). This may take several minutes...\n";
    echo "If the download fails, run this script again to retry.\n";

    if (\file_exists($modelPath)) {
        @\unlink($modelPath);
    }

    $success = false;
    $retries = 3;

    for ($i = 0; $i < $retries; $i++) {
        if (downloadFile($modelUrl, $modelPath) && isValidModel($modelPath)) {
            $success = true;
            break;
        }

        if (\file_exists($modelPath)) {
            @\unlink($modelPath);
        }

        echo "\nDownload attempt ".($i + 1)." failed. Retrying in 5 seconds...\n";
        \sleep(5);
    }

    if (! $success) {
        exit("Error: Failed to download model after {$retries} attempts. Please try again later.\n");
    }
}

echo "\nSetup complete.\n";
echo "Run the AI server using:\n";
echo $os === 'Windows'
    ? "  ai\\start-ai.bat\n"
    : "  chmod +x ai/start-ai.sh && ./ai/start-ai.sh\n";

function ensureDirectory(string $path): void
{
    if (! \is_dir($path)) {
        \mkdir($path, 0755, true);
    }
}

/**
 * @param  list<string>  $keep
 */
function cleanDirectory(string $dir, array $keep = []): void
{
    if (! \is_dir($dir)) {
        return;
    }

    $items = \array_diff(\scandir($dir) ?: [], ['.', '..']);

    foreach ($items as $item) {
        if (\in_array($item, $keep, true)) {
            continue;
        }

        $path = $dir.'/'.$item;

        if (\is_dir($path)) {
            cleanDirectory($path);
            @\rmdir($path);
            continue;
        }

        @\unlink($path);
    }
}

function isEngineUsable(string $enginePath): bool
{
    if (! \file_exists($enginePath)) {
        return false;
    }

    if (PHP_OS_FAMILY !== 'Windows') {
        @\chmod($enginePath, 0755);
    }

    $command = \escapeshellarg($enginePath).' --help 2>&1';
    \exec($command, $output, $exitCode);
    $text = \implode("\n", $output);

    return $exitCode === 0 && \str_contains(\strtolower($text), 'usage');
}

function extractZip(string $archivePath, string $extractDir): bool
{
    $zip = new ZipArchive;

    if ($zip->open($archivePath) !== true) {
        return false;
    }

    $ok = $zip->extractTo($extractDir);
    $zip->close();

    return $ok;
}

function extractTarGz(string $archivePath, string $extractDir): bool
{
    $command = 'tar -xzf '.\escapeshellarg($archivePath).' -C '.\escapeshellarg($extractDir);
    \system($command, $exitCode);

    return $exitCode === 0;
}

function flattenExtractedFiles(string $fromDir, string $toDir): void
{
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($fromDir, FilesystemIterator::SKIP_DOTS),
    );

    foreach ($iterator as $file) {
        if (! $file instanceof SplFileInfo || ! $file->isFile()) {
            continue;
        }

        $target = $toDir.'/'.$file->getBasename();

        if (\file_exists($target)) {
            @\unlink($target);
        }

        @\rename($file->getPathname(), $target);
    }
}

function findInstalledModel(string $modelDir): ?string
{
    $models = \glob($modelDir.'/*.gguf') ?: [];

    foreach ($models as $model) {
        if (isValidModel($model)) {
            return \basename($model);
        }
    }

    return null;
}

function isValidModel(string $path): bool
{
    return \file_exists($path) && \filesize($path) > 1024 * 1024 * 1024;
}

function downloadFile(string $url, string $path): bool
{
    $options = [
        'http' => [
            'method' => 'GET',
            'header' => "User-Agent: FreshLeaf-Setup/1.0\r\n",
            'follow_location' => 1,
            'max_redirects' => 10,
            'timeout' => 300,
        ],
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false,
        ],
    ];

    $ctx = \stream_context_create($options, [
        'notification' => static function (
            int $code,
            int $severity,
            string $message,
            int $messageCode,
            int $bytesTransferred,
            int $bytesMax,
        ): void {
            static $lastOutput = 0.0;

            if ($code !== STREAM_NOTIFY_PROGRESS) {
                return;
            }

            $now = \microtime(true);
            if ($now - $lastOutput < 0.5) {
                return;
            }
            $lastOutput = $now;

            if ($bytesMax > 0) {
                $percent = (int) \floor(($bytesTransferred / $bytesMax) * 100);
                \printf("\rProgress: %d%% (%d/%d MB)", $percent, $bytesTransferred / 1024 / 1024, $bytesMax / 1024 / 1024);
                return;
            }

            \printf("\rProgress: %d MB downloaded...", $bytesTransferred / 1024 / 1024);
        },
    ]);

    try {
        if (@\copy($url, $path, $ctx)) {
            return true;
        }
    } catch (Throwable) {
        // Fall through to the PowerShell fallback on Windows.
    }

    echo "\nPHP download failed. Trying PowerShell download fallback...\n";

    return downloadFileWithPowerShell($url, $path);
}

function downloadFileWithPowerShell(string $url, string $path): bool
{
    if (PHP_OS_FAMILY !== 'Windows') {
        return false;
    }

    $scriptPath = \sys_get_temp_dir().'/freshleaf-ai-download-'.\bin2hex(\random_bytes(6)).'.ps1';
    $script = <<<'POWERSHELL'
param(
    [Parameter(Mandatory = $true)]
    [string] $Uri,

    [Parameter(Mandatory = $true)]
    [string] $OutFile
)

$ErrorActionPreference = 'Stop'
$ProgressPreference = 'Continue'
[Net.ServicePointManager]::SecurityProtocol = [Net.SecurityProtocolType]::Tls12

$directory = Split-Path -Parent $OutFile
if (-not (Test-Path -LiteralPath $directory)) {
    New-Item -ItemType Directory -Path $directory -Force | Out-Null
}

Invoke-WebRequest -Uri $Uri -OutFile $OutFile -UseBasicParsing

if (-not (Test-Path -LiteralPath $OutFile)) {
    throw "Download did not create the expected file."
}

if ((Get-Item -LiteralPath $OutFile).Length -le 0) {
    throw "Download created an empty file."
}
POWERSHELL;

    if (\file_put_contents($scriptPath, $script) === false) {
        return false;
    }

    $command = 'powershell.exe -NoProfile -ExecutionPolicy Bypass -File '
        .\escapeshellarg($scriptPath).' '
        .\escapeshellarg($url).' '
        .\escapeshellarg($path).' 2>&1';

    \exec($command, $output, $exitCode);
    @\unlink($scriptPath);

    if ($exitCode !== 0) {
        echo \implode("\n", $output)."\n";

        return false;
    }

    return \file_exists($path) && \filesize($path) > 0;
}
