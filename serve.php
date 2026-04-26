<?php

/**
 * serve.php — attachment / file serving
 *
 * Standalone entry point. Bootstraps Config + Router, then delegates
 * to Router::serveFile() which handles path resolution, security checks,
 * MIME detection, and output.
 *
 * Expects: ?project={slug}&file={path}
 */

require_once __DIR__ . '/src/Config.php';
require_once __DIR__ . '/src/Router.php';

use Diplodocus\Config;
use Diplodocus\Router;

$config     = Config::getInstance();
$projectsPath = $config->get('projects_paths', $config->get('projects_path'));
$router     = new Router($projectsPath);

if (!$router->isFileRequest()) {
    http_response_code(400);
    exit('Bad request');
}

$router->serveFile();
