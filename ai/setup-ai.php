<?php

declare(strict_types=1);

/**
 * Setup AI script for FreshLeaf project.
 * Downloads the llama-server binary and Phi-3 model.
 */
$os = PHP_OS_FAMILY;
$arch = php_uname('m');

echo "Detected OS: $os, Architecture: $arch\n";

$binDir = __DIR__.'/bin';
$modelDir = __DIR__.'/models';

// Ensure directories exist
if (! is_dir($binDir)) {
    mkdir($binDir, 0755, true);
}
if (! is_dir($modelDir)) {
    mkdir($modelDir, 0755, true);
}

// 1. Download llama-server engine
$llamaVersion = 'b8893'; // Latest stable tag for April 2026
$baseUrl = "https://github.com/ggml-org/llama.cpp/releases/download/$llamaVersion/";

$binaryFile = '';
$isZip = false;

if ($os === 'Windows') {
    $binaryFile = "llama-$llamaVersion-bin-win-cpu-x64.zip";
    $isZip = true;
} elseif ($os === 'Darwin') {
    if (strpos($arch, 'arm') !== false) {
        $binaryFile = "llama-$llamaVersion-bin-macos-arm64.tar.gz";
    } else {
        $binaryFile = "llama-$llamaVersion-bin-macos-x64.tar.gz";
    }
} else {
    $binaryFile = "llama-$llamaVersion-bin-ubuntu-x64.tar.gz";
}

$downloadUrl = $baseUrl.$binaryFile;
$targetPath = $binDir.'/'.$binaryFile;

if (file_exists($binDir.'/llama-server') || file_exists($binDir.'/llama-server.exe')) {
    echo "AI Engine already exists. Skipping engine download.\n";
} else {
    echo "Downloading llama-server engine from $downloadUrl...\n";
    if (! downloadFile($downloadUrl, $targetPath)) {
        exit("Error: Failed to download binary. Please check your internet connection.\n");
    }

    if ($isZip) {
        echo "Extracting engine...\n";
        $zip = new ZipArchive;
        if ($zip->open($targetPath) === true) {
            $zip->extractTo($binDir);
            $zip->close();

            // Handle nested directories in zip
            $items = array_diff(scandir($binDir), ['.', '..', '.gitkeep', $binaryFile]);
            foreach ($items as $item) {
                if (is_dir($binDir.'/'.$item)) {
                    $subItems = array_diff(scandir($binDir.'/'.$item), ['.', '..']);
                    foreach ($subItems as $subItem) {
                        @rename($binDir.'/'.$item.'/'.$subItem, $binDir.'/'.$subItem);
                    }
                    @rmdir($binDir.'/'.$item);
                }
            }
            unlink($targetPath);
            echo "Extraction complete.\n";
        } else {
            exit("Error: Failed to extract zip file.\n");
        }
    } else {
        echo "Extracting tar.gz engine...\n";
        $command = 'tar -xzf '.escapeshellarg($targetPath).' -C '.escapeshellarg($binDir);
        system($command);
        unlink($targetPath);
        echo "Extraction complete.\n";
    }
}

// 2. Download Model (Phi-3 Mini 4K Instruct Q4_K_M)
$modelUrl = 'https://huggingface.co/microsoft/Phi-3-mini-4k-instruct-gguf/resolve/main/Phi-3-mini-4k-instruct-q4.gguf';
$modelPath = $modelDir.'/phi-3-mini-4k-instruct.Q4_K_M.gguf';

// Check if model exists and is valid size (> 1GB)
if (file_exists($modelPath) && filesize($modelPath) > 1024 * 1024 * 1024) {
    echo "Model already exists and seems valid. Skipping download.\n";
} else {
    if (file_exists($modelPath)) {
        unlink($modelPath);
    }

    echo "Downloading Phi-3 model (~2.4GB). This may take several minutes...\n";
    echo "If the download fails, simply run this script again to retry.\n";

    $success = false;
    $retries = 3;

    for ($i = 0; $i < $retries; $i++) {
        if (downloadFile($modelUrl, $modelPath)) {
            // Verify final size
            if (filesize($modelPath) > 1024 * 1024 * 1024) {
                $success = true;
                break;
            } else {
                echo "\nWarning: Download completed but file is too small. Retrying...\n";
                unlink($modelPath);
            }
        }
        echo "\nDownload attempt ".($i + 1)." failed. Retrying in 5 seconds...\n";
        sleep(5);
    }

    if (! $success) {
        exit("Error: Failed to download model after $retries attempts. Please try again later.\n");
    }
}

echo "\nSetup Complete!\n";
echo "Run the AI server using:\n";
if ($os === 'Windows') {
    echo "  ai\\start-ai.bat\n";
} else {
    echo "  chmod +x ai/start-ai.sh\n";
    echo "  ./ai/start-ai.sh\n";
}

/**
 * Helper to download a file with progress indicator and redirect handling
 */
function downloadFile(string $url, string $path): bool
{
    $options = [
        'http' => [
            'method' => 'GET',
            'header' => "User-Agent: FreshLeaf-Setup/1.0\r\n",
            'follow_location' => 1,
            'max_redirects' => 10,
            'timeout' => 300, // 5 minutes timeout per chunk/stream
        ],
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false,
        ],
    ];

    $ctx = stream_context_create($options, [
        'notification' => function ($code, $severity, $message, $message_code, $bytes_transferred, $bytes_max) {
            static $lastOutput = 0;

            if ($code == STREAM_NOTIFY_PROGRESS) {
                // Throttle output to once per 500ms to avoid console flicker and lag
                $now = microtime(true);
                if ($now - $lastOutput < 0.5) {
                    return;
                }
                $lastOutput = $now;

                if ($bytes_max > 0) {
                    $percent = floor(($bytes_transferred / $bytes_max) * 100);
                    printf("\rProgress: %d%% (%d/%d MB)", $percent, $bytes_transferred / 1024 / 1024, $bytes_max / 1024 / 1024);
                } else {
                    // bytes_max might be 0 if the server doesn't send Content-Length
                    printf("\rProgress: %d MB downloaded...", $bytes_transferred / 1024 / 1024);
                }
            }
        },
    ]);

    // Use copy() instead of file_put_contents(fopen) for better large file handling
    try {
        return @copy($url, $path, $ctx);
    } catch (Exception $e) {
        return false;
    }
}
