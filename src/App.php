<?php

/**
 * App - Main application bootstrap and request handler
 */

namespace Diplodocus;

require_once __DIR__ . '/Config.php';
require_once __DIR__ . '/Router.php';
require_once __DIR__ . '/ProjectManager.php';
require_once __DIR__ . '/ContentRenderer.php';
// require_once __DIR__ . '/Validator.php'; // TODO: validation feature disabled
require_once __DIR__ . '/TemplateEngine.php';

class App
{
    private Config $config;
    private Router $router;
    private ProjectManager $projectManager;
    private ContentRenderer $renderer;
    // private Validator $validator; // TODO: validation feature disabled
    private TemplateEngine $template;

    public function __construct()
    {
        $this->config = Config::getInstance();
        $basePath = $this->config->get('base_path');
        $projectsPath = $this->config->get('projects_paths', $this->config->get('projects_path'));

        $this->router = new Router($projectsPath);
        $this->projectManager = new ProjectManager($projectsPath);
        $this->renderer = new ContentRenderer($projectsPath);
        // $this->validator = new Validator($basePath); // TODO: validation feature disabled
        $this->template = new TemplateEngine($this->config->get('templates_path'));
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
        $route   = $this->router->route();
        $project = $route['project'];
        $page    = $route['page'];

        // Get projects and pages
        $projects = $this->projectManager->getProjects();
        $pages = [];
        $content = null;
        $toc = [];
        $pageTitle = null;
        $validationResults = null;
        $searchIndex = [];

        // TODO: validation feature disabled
        // if (isset($_GET['validate'])) {
        //     $validationResults = $this->validator->validateAll();
        // }

        // Get pages for current project (no auto-redirect — home = no project)
        if ($project) {
            $pages = $this->projectManager->getPages($project);

            // Auto-load first page when landing on a space with no page selected
            if (!$page && !empty($pages)) {
                $firstSlug = $pages[0]['slug'];
                $this->router->redirect($this->router->url(['project' => $project, 'page' => $firstSlug]));
                return;
            }

            // Global search index across all pages in the project
            $searchIndex = $this->renderer->buildProjectSearchIndex($project);

            if ($page) {
                $rendered = $this->renderer->render($project, $page);
                if ($rendered) {
                    $content    = $rendered['html'];
                    $toc        = $rendered['toc'];
                    $pageTitle  = $rendered['title'];
                    $projectContext = $rendered['project'] ?? null;
                    $pageIndex  = $rendered['pageIndex'] ?? false;
                    $pageCount  = $rendered['pageCount'] ?? null;
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
            'project' => $projectContext ?? null,
            'pageIndex' => $pageIndex ?? false,
            'pageCount' => $pageCount ?? null,
            'searchIndex' => $searchIndex ?? [],
            //TODO: can go
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
        $privateProject = $this->config->get('private_projects', []);

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
        $isPrivate = $project && in_array($project, $privateProject, true);
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
     * Render the error page via templates/error.php.
     * Falls back to a plain inline string if the template cannot be loaded,
     * so error handling itself never throws.
     */
    private function renderErrorPage(
        string $type    = '',
        string $message = '',
        string $file    = '',
        int    $line    = 0,
        string $trace   = ''
    ): string {
        $data = [
            'appName' => $this->config->get('app_name', 'Diplodocus'),
            'debug'   => !empty($type),
            'type'    => $type,
            'message' => $message,
            'file'    => $file,
            'line'    => $line,
            'trace'   => $trace,
        ];

        $templateFile = $this->config->get('templates_path') . DIRECTORY_SEPARATOR . 'error.php';

        if (file_exists($templateFile)) {
            try {
                return $this->template->partial('error', $data);
            } catch (\Throwable $ignored) {
                // Template itself broke — fall through to inline fallback
            }
        }

        // Minimal inline fallback (no external dependencies)
        $appName = htmlspecialchars($data['appName'], ENT_QUOTES, 'UTF-8');
        return "<!DOCTYPE html><html lang=\"en\"><head><meta charset=\"UTF-8\"><title>500 — {$appName}</title></head>"
            . "<body style=\"font-family:system-ui;background:#0f1117;color:#e2e8f0;padding:2rem\">"
            . "<h1>500 — Something went wrong</h1><p><a href=\"/\" style=\"color:#63b3ed\">Return home</a></p>"
            . ($data['debug'] ? "<pre style=\"margin-top:1rem;white-space:pre-wrap\">{$type}: {$message}\n{$trace}</pre>" : '')
            . "</body></html>";
    }

    /**
     * Get config instance
     */
    public function getConfig(): Config
    {
        return $this->config;
    }

    // TODO: validation feature disabled
    // public function getValidator(): Validator
    // {
    //     return $this->validator;
    // }
}
