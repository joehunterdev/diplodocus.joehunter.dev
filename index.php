<?php

/**
 * Diplodocus — Markdown-first documentation site
 *
 * Bootstrap: load the engine and run.
 * All configuration lives in config.php (optional) or src/Config.php defaults.
 */

declare(strict_types=1);

define('DIPLODOCUS_ROOT', __DIR__);

require_once __DIR__ . '/src/App.php';

(new Diplodocus\App())->run();
