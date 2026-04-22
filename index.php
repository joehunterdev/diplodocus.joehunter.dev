<?php

declare(strict_types=1);

/**
 * Diplodocus — Markdown-first documentation site
 *
 * Bootstrap: load the engine and run.
 * Error display and logging are driven by config.php:
 *   'debug'     => true          — show errors in browser
 *   'error_log' => '/path/to/file' — log errors to file
 */

(static function (): void {
    $cfg = is_file(__DIR__ . '/config.php') ? require __DIR__ . '/config.php' : [];
    $debug  = !empty($cfg['debug']);
    $logFile = $cfg['error_log'] ?? '';

    ini_set('display_errors', $debug ? '1' : '0');
    ini_set('display_startup_errors', $debug ? '1' : '0');
    ini_set('log_errors', $logFile ? '1' : '0');
    if ($logFile) ini_set('error_log', $logFile);
    error_reporting($debug || $logFile ? E_ALL : 0);
})();

define('DIPLODOCUS_ROOT', __DIR__);

require_once __DIR__ . '/src/App.php';

(new Diplodocus\App())->run();
