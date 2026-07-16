<?php

/**
 * Diplodocus — local configuration
 *
 * Only override what you actually need to change.
 * Everything else is hardcoded in templates/ or src/Config.php.
 */
return [
    'app_name' => 'Diplodocus',
    'version'  => '1.0.1',

    // Content folders scanned for projects (in order)
    'projects_paths' => [
        __DIR__ . '/public_md',
        __DIR__ . '/private_md',
    ],

    // Canonical origin for SEO (sitemap.xml, robots.txt, canonical/og URLs)
    'site_url' => 'https://diplodocus.joehunter.dev',

    // Error handling
    // true  = show full exception + stack trace (never in production)
    'debug'     => false,
    'error_log' => '',
];
