<?php

$dir = new RecursiveDirectoryIterator('app/Filament');
$iterator = new RecursiveIteratorIterator($dir);
$files = new RegexIterator($iterator, '/^.+\.php$/i', RecursiveRegexIterator::GET_MATCH);

foreach ($files as $file) {
    $path = $file[0];
    if (strpos($path, 'Pages') !== false) {
        continue;
    }

    $content = file_get_contents($path);
    if (strpos($content, 'getNavigationBadge') !== false) {
        $pattern1 = '/\s+public static function getNavigationBadge\(\)\: \?string\s*\{\s*\$count = static\:\:getEloquentQuery\(\)->count\(\);\s*return \$count > 0 \? \(string\) \$count \: null;\s*\}/';
        $newContent = preg_replace($pattern1, '', $content);

        $pattern2 = '/\s+public static function getNavigationBadgeColor\(\)\: string\|array\|null\s*\{\s*return \'(.*?)\';\s*\}/';
        $newContent = preg_replace($pattern2, '', $newContent);

        if ($newContent !== $content) {
            file_put_contents($path, $newContent);
            echo "Removed from $path\n";
        } else {
            echo "Regex failed for $path\n";
        }
    }
}
