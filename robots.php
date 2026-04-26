<?php

/**
 * robots.txt — dynamic robots file
 *
 * Standalone entry point. Only needs Config.
 */

require_once __DIR__ . '/src/Config.php';

use Diplodocus\Config;

$config        = Config::getInstance();
$siteUrl       = rtrim($config->get('site_url', ''), '/');
$privateSpaces = $config->get('private_projects', []);

header('Content-Type: text/plain; charset=utf-8');

echo "User-agent: *\n";
echo "Allow: /\n";

foreach ($privateSpaces as $space) {
    echo 'Disallow: /' . rawurlencode($space) . "/\n";
}

if ($siteUrl) {
    echo "\nSitemap: {$siteUrl}/sitemap.xml\n";
}
