<?php

/**
 * Router - Handles URL routing and file serving
 */

namespace Diplodocus;

class Router
{
    private string $projectsPath;
    private array $params = [];

    public function __construct(string $projectsPath)
    {
        $this->projectsPath = rtrim($projectsPath, '/\\');
        $this->parseRequest();
    }

    /**
     * Parse the current request — clean paths take priority over query params.
     *
     * URL patterns:
     *   /                          → home
     *   /{space}/                  → space landing
     *   /{space}/{page}            → page view
     *
     * Legacy query-param form still works as fallback:
     *   /?project={space}&page={page}
     */
    private function parseRequest(): void
    {
        // Strip query string and leading/trailing slashes
        $uri  = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        $uri  = trim($uri, '/');
        $segs = ($uri !== '') ? explode('/', $uri) : [];

        // Special system routes
        if ($uri === 'sitemap.xml') {
            $this->params = ['type' => 'sitemap', 'project' => null, 'page' => null, 'file' => null, 'action' => null];
            return;
        }
        if ($uri === 'robots.txt') {
            $this->params = ['type' => 'robots', 'project' => null, 'page' => null, 'file' => null, 'action' => null];
            return;
        }

        // Segment 0 = space, segment 1 = page — fall back to ?project / ?page
        $project = !empty($segs[0]) ? $segs[0] : ($_GET['project'] ?? null);
        $page    = !empty($segs[1]) ? $segs[1] : ($_GET['page']    ?? null);

        $this->params = [
            'project' => $project,
            'page'    => $page,
            'file'    => $_GET['file']   ?? null,
            'action'  => $_GET['action'] ?? null,
        ];
    }

    /**
     * Resolve the current request into a structured route.
     * Returns: ['type' => 'file'|'page', 'project' => ?, 'page' => ?, 'file' => ?]
     */
    public function route(): array
    {
        // System routes
        if (isset($this->params['type']) && in_array($this->params['type'], ['sitemap', 'robots'], true)) {
            return ['type' => $this->params['type'], 'project' => null, 'page' => null, 'file' => null];
        }

        if ($this->isFileRequest()) {
            return [
                'type'    => 'file',
                'project' => $this->params['project'],
                'file'    => $this->params['file'],
                'page'    => null,
            ];
        }

        return [
            'type'    => 'page',
            'project' => $this->params['project'],
            'page'    => $this->params['page'],
            'file'    => null,
        ];
    }

    /**
     * Get a request parameter
     */
    public function get(string $key, $default = null)
    {
        return $this->params[$key] ?? $default;
    }

    /**
     * Check if this is a file request
     */
    public function isFileRequest(): bool
    {
        return isset($this->params['file']) && isset($this->params['project']);
    }

    /**
     * Handle file serving (images, attachments)
     */
    public function serveFile(): bool
    {
        if (!$this->isFileRequest()) {
            return false;
        }

        $requestedFile = $this->params['file'];
        $projectSlug = $this->params['project'];

        // Security: only allow files from within project directories
        $projectPath = $this->projectsPath . DIRECTORY_SEPARATOR . $projectSlug;
        $filePath = realpath($projectPath . DIRECTORY_SEPARATOR . $requestedFile);

        // Verify file is within project directory (prevent directory traversal)
        if ($filePath && strpos($filePath, realpath($projectPath)) === 0 && file_exists($filePath)) {
            $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
            $mimeTypes = [
                'png' => 'image/png',
                'jpg' => 'image/jpeg',
                'jpeg' => 'image/jpeg',
                'gif' => 'image/gif',
                'svg' => 'image/svg+xml',
                'webp' => 'image/webp',
                'pdf' => 'application/pdf',
                'csv' => 'text/csv',
                'json' => 'application/json',
            ];

            if (isset($mimeTypes[$extension])) {
                header('Content-Type: ' . $mimeTypes[$extension]);
                header('Content-Length: ' . filesize($filePath));
                header('Cache-Control: public, max-age=86400');
                readfile($filePath);
                return true;
            }
        }

        // File not found or not allowed
        http_response_code(404);
        echo 'File not found';
        return true;
    }

    /**
     * Redirect to a URL
     */
    public function redirect(string $url): void
    {
        header("Location: $url");
        exit;
    }

    /**
     * Build a clean URL.
     *
     *   url([])                            → /
     *   url(['project' => 'foo'])          → /foo/
     *   url(['project' => 'foo',
     *        'page'    => 'bar'])          → /foo/bar
     *
     * Special actions (validate, download) keep query-param form.
     */
    public function url(array $params = []): string
    {
        // Special-case actions that stay as query params
        if (!empty($params['action']) || isset($params['validate'])) {
            return '/?' . http_build_query($params);
        }

        $project = $params['project'] ?? null;
        $page    = $params['page']    ?? null;

        if (!$project) return '/';
        if (!$page)    return '/' . rawurlencode($project) . '/';
        return '/' . rawurlencode($project) . '/' . rawurlencode($page);
    }

    /**
     * Get the current project slug
     */
    public function getProject(): ?string
    {
        return $this->params['project'];
    }

    /**
     * Get the current page slug
     */
    public function getPage(): ?string
    {
        return $this->params['page'];
    }
}
