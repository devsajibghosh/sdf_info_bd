<?php

$projectRoot = __DIR__; // or specify manually like: '/var/www/html/my-laravel-project'
$outputFile = $projectRoot . '/translations.txt';

$translations = [];

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($projectRoot, RecursiveDirectoryIterator::SKIP_DOTS)
);

// Go through all files except the vendor folder
foreach ($iterator as $file) {
    if (strpos($file->getPathname(), '/vendor/') !== false) {
        continue;
    }

    if ($file->isFile() && in_array($file->getExtension(), ['php', 'blade.php'])) {
        $contents = file_get_contents($file->getPathname());

        // Match __() and @lang()
        preg_match_all("/__\(\s*['\"](.*?)['\"]\s*\)/", $contents, $matches1);
        preg_match_all("/@lang\(\s*['\"](.*?)['\"]\s*\)/", $contents, $matches2);

        $translations = array_merge($translations, $matches1[1], $matches2[1]);
    }
}

// Remove duplicates and sort
$translations = array_unique($translations);
sort($translations);

// Save to file
file_put_contents($outputFile, implode("\n", $translations));

echo "✅ Translations extracted to: $outputFile" . PHP_EOL;
