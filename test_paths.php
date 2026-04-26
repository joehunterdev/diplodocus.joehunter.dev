<?php
require __DIR__ . '/src/Config.php';
$c = Diplodocus\Config::getInstance();
$paths = $c->get('projects_paths', $c->get('projects_path'));
var_dump($paths);

// Simulate serveFile lookup
$project = 'getting-started';
$file = 'attachments/11a-theme-picker.svg';
foreach ((array)$paths as $basePath) {
    $basePath = rtrim($basePath, '/\\');
    $candidate = $basePath . DIRECTORY_SEPARATOR . $project;
    echo "Checking: $candidate\n";
    echo "  is_dir: " . (is_dir($candidate) ? 'yes' : 'no') . "\n";
    if (is_dir($candidate)) {
        $fp = realpath($candidate . DIRECTORY_SEPARATOR . $file);
        echo "  realpath: " . ($fp ?: 'NULL') . "\n";
    }
}
