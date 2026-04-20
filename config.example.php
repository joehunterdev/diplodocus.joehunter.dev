<?php

/**
 * Diplodocus — User configuration
 *
 * Copy this file to `config.php` and edit as needed.
 * All keys are optional; anything you omit falls back to the defaults in
 * src/Config.php.
 */
return [
    // Basic branding — shown in <title>, sidebar header, and breadcrumbs
    'app_name' => 'Diplodocus',

    // Optional logo. Leave empty for a text-only wordmark.
    'logo_url' => '',

    // Stylesheets loaded in order. theme.css MUST come first so its CSS
    // variables are available to every stylesheet that follows.
    'stylesheets' => [
        'assets/css/theme.css',
        'assets/css/tailwind.min.css',
        'assets/css/highlight-dark.min.css',
        'assets/css/diplodocus.css',
    ],

    // Scripts loaded at the end of <body>
    'scripts' => [
        'assets/js/highlight.min.js',
        'assets/js/app.js',
    ],

    // Theme defaults — flip <html data-theme="dark"> to activate dark mode
    'default_theme' => 'light',
    'enable_dark_mode' => true,

    // Folders to hide from project listing
    'excluded_dirs' => [
        'src',
        'lib',
        'assets',
        'templates',
        '.spaces',
        '.vscode',
        '.backup',
        '.git',
        '.claude',
        'vendor',
        'node_modules',
    ],

    // Validation gates
    'block_on_security_issues' => true,
    'block_on_lint_issues'     => false,

    // Error handling
    // true  = show full exception details (never use in production)
    // false = show a generic 500 page
    'debug'     => false,
    // Absolute path to a writable log file, e.g. __DIR__ . '/logs/error.log'
    // Leave empty to disable file logging.
    'error_log' => '',
];
