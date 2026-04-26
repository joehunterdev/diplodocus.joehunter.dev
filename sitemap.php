<?php

/**
 * sitemap.xml — dynamic XML sitemap
 *
 * Standalone entry point. Bootstraps only what it needs:
 * Config, ProjectManager (to enumerate pages), Router (for URL generation).
 */

require_once __DIR__ . '/src/Config.php';
require_once __DIR__ . '/src/ProjectManager.php';
require_once __DIR__ . '/src/Router.php';

use Diplodocus\Config;
use Diplodocus\ProjectManager;
use Diplodocus\Router;

$config         = Config::getInstance();
$spacesPath     = $config->get('projects_paths', $config->get('projects_path'));
$siteUrl        = rtrim($config->get('site_url', ''), '/');
$privateSpaces  = $config->get('private_spaces', []);

$projectManager = new ProjectManager($spacesPath);
$router         = new Router($spacesPath);

header('Content-Type: application/xml; charset=utf-8');

$urls = [];

// Home
$urls[] = ['loc' => $siteUrl . '/', 'changefreq' => 'weekly', 'priority' => '1.0'];

foreach ($projectManager->getProjects() as $proj) {
    $slug = $proj['slug'];
    if (in_array($slug, $privateSpaces, true)) {
        continue;
    }
    foreach ($projectManager->getPages($slug) as $p) {
        $urls[] = [
            'loc'        => $siteUrl . $router->url(['project' => $slug, 'page' => $p['slug']]),
            'changefreq' => 'monthly',
            'priority'   => '0.8',
        ];
    }
}

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
foreach ($urls as $u) {
    echo "  <url>\n";
    echo "    <loc>" . htmlspecialchars($u['loc'], ENT_QUOTES, 'UTF-8') . "</loc>\n";
    echo "    <changefreq>{$u['changefreq']}</changefreq>\n";
    echo "    <priority>{$u['priority']}</priority>\n";
    echo "  </url>\n";
}
echo '</urlset>';
