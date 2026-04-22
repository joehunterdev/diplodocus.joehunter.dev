<?php

declare(strict_types=1);

/**
 * Diplodocus — Markdown-first documentation site
 *
 * Bootstrap: load the engine and run.
 * All configuration lives in config.php (optional) or src/Config.php defaults.
 */

// Read config early so the debug flag can control PHP's own error display.
// This catches fatal errors that occur before the app's try/catch runs.
$_diplo_cfg = is_file(__DIR__ . '/config.php') ? (require __DIR__ . '/config.php') : [];
if (!empty($_diplo_cfg['debug'])) {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    error_reporting(0);
}
unset($_diplo_cfg);

define('DIPLODOCUS_ROOT', __DIR__);

require_once __DIR__ . '/src/App.php';

(new Diplodocus\App())->run();
