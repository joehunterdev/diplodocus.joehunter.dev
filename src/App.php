<?php

/**
 * App - Main application bootstrap and request handler
 */

namespace Diplodocus;

require_once __DIR__ . '/Config.php';
require_once __DIR__ . '/Router.php';
require_once __DIR__ . '/ProjectManager.php';
require_once __DIR__ . '/ContentRenderer.php';
require_once __DIR__ . '/Validator.php';
require_once __DIR__ . '/TemplateEngine.php';

class App
{
    private Config $config;
    private Router $router;
    private ProjectManager $projectManager;
    private ContentRenderer $renderer;
    private Validator $validator;
    private TemplateEngine $template;

    public function __construct()
    {
        $this->config = Config::getInstance();
        $this->configureErrorHandling();

        $basePath = $this->config->get('base_path');
        $spacesPath = $this->config->get('spaces_path');
        $excludedDirs = $this->config->get('excluded_dirs', []);

        $this->router = new Router($spacesPath);
        $this->projectManager = new ProjectManager($spacesPath, $excludedDirs);
        $this->renderer = new ContentRenderer($spacesPath);
        $this->validator = new Validator($basePath);
        $this->template = new TemplateEngine($this->config->get('templates_path'));
    }

    /**
     * Apply PHP error display and logging from config.
     * Runs immediately after Config is loaded, before anything else.
     */
    private function configureErrorHandling(): void
    {
        $debug   = $this->config->get('debug', false);
        $logFile = $this->config->get('error_log', '');

        ini_set('display_errors', $debug ? '1' : '0');
        ini_set('display_startup_errors', $debug ? '1' : '0');
        ini_set('log_errors', $logFile ? '1' : '0');
        if ($logFile) ini_set('error_log', $logFile);
        error_reporting($debug || $logFile ? E_ALL : 0);
    }

    /**
     * Run the application
     */
    public function run(): void
    {
        try {
            $this->dispatch();
        } catch (\Throwable $e) {
            $this->handleError($e);
        }
    }

    /**
     * Core dispatch — everything that used to live in run()
     */
    private function dispatch(): void
    {
        // Route the request
        $route = $this->router->route();

        // Handle attachment / file requests
        if ($route['type'] === 'file') {
            $this->router->serveFile();
            return;
        }

        // Handle sitemap.xml
        if ($route['type'] === 'sitemap') {
            $this->serveSitemap();
            return;
        }

        // Handle robots.txt
        if ($route['type'] === 'robots') {
            $this->serveRobots();
            return;
        }

        // Handle documentation requests
        $project = $route['project'];
        $page = $route['page'];

        // Get projects and pages
        $projects = $this->projectManager->getProjects();
        $pages = [];
        $content = null;
        $toc = [];
        $pageTitle = null;
        $validationResults = null;

        // Validate if requested
        if (isset($_GET['validate'])) {
            $validationResults = $this->validator->validateAll();
        }

        // Get pages for current project (no auto-redirect — home = no project)
        if ($project) {
            $pages = $this->projectManager->getPages($project);

            // Auto-load first page when landing on a space with no page selected
            if (!$page && !empty($pages)) {
                $firstSlug = $pages[0]['slug'];
                $this->router->redirect($this->router->url(['project' => $project, 'page' => $firstSlug]));
                return;
            }

            if ($page) {
                $rendered = $this->renderer->render($project, $page);
                if ($rendered) {
                    $content    = $rendered['html'];
                    $toc        = $rendered['toc'];
                    $pageTitle  = $rendered['title'];
                }
            }
        }

        // Prepare template data
        $data = [
            'config' => $this->config,
            'projects' => $projects,
            'pages' => $pages,
            'currentProject' => $project,
            'currentPage' => $page,
            'content' => $content,
            'toc' => $toc,
            'validationResults' => $validationResults,
            'hasSecurityIssues' => $validationResults ? !empty($validationResults['security']) : false,
            'hasLintIssues' => $validationResults ? !empty($validationResults['lint']) : false,
            'seo' => $this->buildSeo($project, $page, $pageTitle, $content),
        ];

        // Add global template data
        $this->template->addGlobal('appName', $this->config->get('app_name'));
        $this->template->addGlobal('logoUrl', $this->config->get('logo_url'));
        $this->template->addGlobal('router', $this->router);

        // Render through the template engine — single rendering path.
        // All escaping goes through T::e() in templates/.
        $this->template->setLayout('layout');
        echo $this->template->render('content', $data);
    }

    // -------------------------------------------------------------------------
    // SEO helpers
    // -------------------------------------------------------------------------

    /**
     * Build per-request SEO metadata array passed to templates as $seo.
     */
    private function buildSeo(?string $project, ?string $page, ?string $pageTitle, ?string $htmlContent): array
    {
        $appName     = $this->config->get('app_name', 'Diplodocus');

        $siteUrl     = rtrim($this->config->get('site_url', ''), '/');
        $siteDesc    = $this->config->get('site_description', '');
        $ogImage     = $this->config->get('og_image', '/example.png');
        $authorUrl   = $this->config->get('author_url', '');
        $privateSpaces = $this->config->get('private_spaces', []);

        // Build canonical URL
        $path      = $this->router->url(['project' => $project, 'page' => $page]);
        $canonical = $siteUrl ? $siteUrl . $path : '';

        // Determine og:image absolute URL
        $ogImageAbsolute = (strpos($ogImage, 'http') === 0) ? $ogImage : $siteUrl . $ogImage;

        // Build title
        if ($page && $project) {
            $resolvedTitle = $pageTitle ?? ucfirst(str_replace(['-', '_'], ' ', $page));
            $title = $resolvedTitle . ' — ' . ucfirst(str_replace(['-', '_'], ' ', $project)) . ' — ' . $appName;
        } elseif ($project) {
            $title = ucfirst(str_replace(['-', '_'], ' ', $project)) . ' — ' . $appName;
        } else {
            $title = $appName . ($siteDesc ? ' — ' . $siteDesc : '');
        }

        // Build description (first 160 chars of page text, or site description)
        $description = $siteDesc;
        if ($htmlContent) {
            $plain = trim(strip_tags($htmlContent));
            $plain = preg_replace('/\s+/', ' ', $plain);
            if (strlen($plain) > 0) {
                $description = substr($plain, 0, 157);
                if (strlen($plain) > 157) {
                    $description .= '...';
                }
            }
        }

        // Robots directive
        $isPrivate = $project && in_array($project, $privateSpaces, true);
        $robots = $isPrivate ? 'noindex,nofollow' : 'index,follow';

        return [
            'title'         => $title,
            'description'   => $description,
            'canonical'     => $canonical,
            'ogImage'       => $ogImageAbsolute,
            'authorUrl'     => $authorUrl,
            'robots'        => $robots,
        ];
    }

    /**
     * Serve a dynamic sitemap.xml
     */
    private function serveSitemap(): void
    {
        $siteUrl      = rtrim($this->config->get('site_url', ''), '/');
        $privateSpaces = $this->config->get('private_spaces', []);
        $projects     = $this->projectManager->getProjects();

        header('Content-Type: application/xml; charset=utf-8');

        $urls = [];

        // Home
        $urls[] = [
            'loc'        => $siteUrl . '/',
            'changefreq' => 'weekly',
            'priority'   => '1.0',
        ];

        foreach ($projects as $proj) {
            $slug = $proj['slug'];
            if (in_array($slug, $privateSpaces, true)) {
                continue;
            }
            $pages = $this->projectManager->getPages($slug);
            foreach ($pages as $p) {
                $urls[] = [
                    'loc'        => $siteUrl . $this->router->url(['project' => $slug, 'page' => $p['slug']]),
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
    }

    /**
     * Serve a dynamic robots.txt
     */
    private function serveRobots(): void
    {
        $siteUrl      = rtrim($this->config->get('site_url', ''), '/');
        $privateSpaces = $this->config->get('private_spaces', []);

        header('Content-Type: text/plain; charset=utf-8');

        echo "User-agent: *\n";
        echo "Allow: /\n";
        foreach ($privateSpaces as $space) {
            echo 'Disallow: /' . rawurlencode($space) . "/\n";
        }
        if ($siteUrl) {
            echo "\nSitemap: {$siteUrl}/sitemap.xml\n";
        }
    }

    /**
     * Handle a caught error/exception
     */
    private function handleError(\Throwable $e): void
    {
        $debug = $this->config->get('debug', false);
        $logPath = $this->config->get('error_log', '');

        // Always log if a path is configured
        if ($logPath) {
            $timestamp = date('Y-m-d H:i:s');
            $entry = "[{$timestamp}] " . get_class($e) . ": " . $e->getMessage()
                . " in " . $e->getFile() . ":" . $e->getLine() . PHP_EOL
                . $e->getTraceAsString() . PHP_EOL . PHP_EOL;
            error_log($entry, 3, $logPath);
        }

        if (!headers_sent()) {
            http_response_code(500);
        }

        if ($debug) {
            // Detailed developer view
            echo $this->renderErrorPage(
                get_class($e),
                htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'),
                htmlspecialchars($e->getFile(), ENT_QUOTES, 'UTF-8'),
                $e->getLine(),
                htmlspecialchars($e->getTraceAsString(), ENT_QUOTES, 'UTF-8')
            );
        } else {
            // Generic user-facing error page
            echo $this->renderErrorPage();
        }
    }

    /**
     * Render a minimal error page (no template dependency)
     */
    private function renderErrorPage(
        string $type    = '',
        string $message = '',
        string $file    = '',
        int    $line    = 0,
        string $trace   = ''
    ): string {
        $appName = htmlspecialchars($this->config->get('app_name', 'Diplodocus'), ENT_QUOTES, 'UTF-8');
        $debug   = !empty($type);

        $detail = '';
        if ($debug) {
            $detail = <<<HTML
            <div class="err-detail">
                <p class="err-type">{$type}</p>
                <p class="err-msg">{$message}</p>
                <p class="err-location">{$file} <strong>:{$line}</strong></p>
                <pre class="err-trace">{$trace}</pre>
            </div>
HTML;
        }

        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>500 — {$appName}</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body   { font-family: system-ui, sans-serif; background: #0f1117; color: #e2e8f0; display: flex; align-items: center; justify-content: center; min-height: 100vh; padding: 2rem; }
        .wrap  { max-width: 680px; width: 100%; }
        .code  { font-size: 5rem; font-weight: 800; color: #e53e3e; line-height: 1; margin-bottom: .5rem; }
        h1     { font-size: 1.5rem; font-weight: 600; margin-bottom: .75rem; }
        p      { color: #94a3b8; font-size: .95rem; line-height: 1.6; }
        a      { color: #63b3ed; }
        .err-detail    { margin-top: 2rem; border-top: 1px solid #2d3748; padding-top: 1.5rem; }
        .err-type      { font-size: .8rem; font-weight: 700; color: #e53e3e; text-transform: uppercase; letter-spacing: .05em; margin-bottom: .35rem; }
        .err-msg       { font-size: 1rem; color: #e2e8f0; margin-bottom: .75rem; }
        .err-location  { font-size: .8rem; color: #718096; margin-bottom: 1rem; }
        .err-location strong { color: #e2e8f0; }
        .err-trace     { background: #1a202c; border: 1px solid #2d3748; border-radius: 6px; padding: 1rem; font-size: .75rem; line-height: 1.6; overflow-x: auto; color: #a0aec0; white-space: pre-wrap; word-break: break-all; }
    </style>
</head>
<body>
    <div class="wrap">
        <div class="code">500</div>
        <h1>Something went wrong</h1>
        <p>An error occurred while rendering this page. Please try again or <a href="?">return home</a>.</p>
        {$detail}
    </div>
</body>
</html>
HTML;
    }

    /**
     * Get config instance
     */
    public function getConfig(): Config
    {
        return $this->config;
    }

    /**
     * Get validator instance
     */
    public function getValidator(): Validator
    {
        return $this->validator;
    }
}
