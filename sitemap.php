<?php

/**
 * sitemap.xml — dynamic XML sitemap
 *
 * Standalone entry point. Bootstraps only what it needs:
 * Config, ProjectManager (to enumerate pages), Router (for URL generation).
 */

require_once __DIR__ . '/src/Config.php';
require_once __DIR__ . '/src/Spec/SpecHandler.php';
require_once __DIR__ . '/src/Spec/FlatNumberedSpec.php';
require_once __DIR__ . '/src/Spec/FeatureDrivenSpec.php';
require_once __DIR__ . '/src/ProjectManager.php';
require_once __DIR__ . '/src/Router.php';

use Diplodocus\Config;
use Diplodocus\ProjectManager;
use Diplodocus\Router;

$config       = Config::getInstance();
$projectsPath = $config->get('projects_path'); // public_md only — private spaces are never indexed

// Use configured site_url, or fall back to the current HTTP origin
$siteUrl = rtrim($config->get('site_url', ''), '/');
if ($siteUrl === '') {
    $scheme  = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host    = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $siteUrl = $scheme . '://' . $host;
}

$projectManager = new ProjectManager($projectsPath);
$router         = new Router($projectsPath);

header('Content-Type: application/xml; charset=utf-8');

$urls = [];
$newestMtime = 0;

$privateProjects = $config->get('private_projects', []);

foreach ($projectManager->getProjects() as $proj) {
    $slug = $proj['slug'];
    if (in_array($slug, $privateProjects, true)) {
        continue;
    }
    foreach ($projectManager->getPages($slug) as $p) {
        $mtime = (isset($p['path']) && is_file($p['path'])) ? filemtime($p['path']) : 0;
        if ($mtime > $newestMtime) {
            $newestMtime = $mtime;
        }
        $urls[] = [
            'loc'        => $siteUrl . $router->url(['project' => $slug, 'page' => $p['slug']]),
            'lastmod'    => $mtime,
            'changefreq' => 'monthly',
            'priority'   => '0.8',
        ];
    }
}

// Home first — its lastmod is the newest page across the site
array_unshift($urls, [
    'loc'        => $siteUrl . '/',
    'lastmod'    => $newestMtime,
    'changefreq' => 'weekly',
    'priority'   => '1.0',
]);

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
foreach ($urls as $u) {
    echo "  <url>\n";
    echo "    <loc>" . htmlspecialchars($u['loc'], ENT_QUOTES, 'UTF-8') . "</loc>\n";
    if (!empty($u['lastmod'])) {
        echo "    <lastmod>" . gmdate('Y-m-d', $u['lastmod']) . "</lastmod>\n";
    }
    echo "    <changefreq>{$u['changefreq']}</changefreq>\n";
    echo "    <priority>{$u['priority']}</priority>\n";
    echo "  </url>\n";
}
echo '</urlset>';
