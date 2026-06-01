<?php

$dir = __DIR__ . '/database/seeders';
$files = glob($dir . '/*.php');

foreach ($files as $file) {
    if (basename($file) === 'DatabaseSeeder.php') continue;

    $content = file_get_contents($file);

    if (strpos($content, 'DB::transaction') !== false) {
        continue;
    }

    if (strpos($content, 'use Illuminate\Support\Facades\DB;') === false) {
        $content = preg_replace('/(use Illuminate\\\\Database\\\\Seeder;)/', "$1\nuse Illuminate\Support\Facades\DB;", $content);
    }

    // A better approach is to use regex with balance. 
    // Since seeders are well-formatted, we can find the start of the `public function run(): void`
    // Then we find the `{` and the matching `}`.

    $tokens = token_get_all($content);
    $output = '';
    $inRun = false;
    $braceCount = 0;
    $runStart = false;

    foreach ($tokens as $token) {
        if (is_array($token)) {
            $text = $token[1];
            if ($token[0] === T_STRING && $text === 'run') {
                $inRun = true;
            }
            $output .= $text;
        } else {
            $text = $token;
            if ($inRun && $text === '{') {
                $braceCount++;
                if ($braceCount === 1) {
                    $output .= $text;
                    $output .= "\n        DB::transaction(static function () {";
                    $runStart = true;
                    continue;
                }
            } elseif ($inRun && $text === '}') {
                $braceCount--;
                if ($braceCount === 0) {
                    $output .= "        });\n    ";
                    $output .= $text;
                    $inRun = false;
                    $runStart = false;
                    continue;
                }
            }
            $output .= $text;
        }
    }

    file_put_contents($file, $output);
    echo "Updated " . basename($file) . "\n";
}
