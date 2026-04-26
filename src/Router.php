<?php

/**
 * Router - Handles URL routing and file serving
 */

namespace Diplodocus;

class Router
{
    private array $projectsPaths;
    private array $params = [];

    public function __construct($projectsPath)
    {
        if (is_array($projectsPath)) {
            $this->projectsPaths = array_map(function ($p) {
                return rtrim($p, '/\\');
            }, $projectsPath);
        } else {
            $this->projectsPaths = [rtrim($projectsPath, '/\\')];
        }
        $this->parseRequest();
    }

    /**
     * Parse the current request — clean paths take priority over query params.
     *
     * URL patterns:
     *   /                          → home
     *   /{project}/                  → space landing
     *   /{project}/{page}            → page view
     *
     * Legacy query-param form still works as fallback:
     *   /?project={project}&page={page}
     */
    private function parseRequest(): void
    {
        // Strip query string and leading/trailing slashes
        $uri  = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        $uri  = trim($uri, '/');
        $segs = ($uri !== '') ? explode('/', $uri) : [];

        // Segment 0 = space, segment 1 = page — fall back to ?project / ?page
        $project = !empty($segs[0]) ? $segs[0] : ($_GET['project'] ?? null);
        $page    = !empty($segs[1]) ? $segs[1] : ($_GET['page']    ?? null);

        $this->params = [
            'project' => $this->sanitizeSlug($project),
            'page'    => $this->sanitizeSlug($page),
            'file'    => $_GET['file']   ?? null,
            'action'  => $_GET['action'] ?? null,
        ];
    }

    /**
     * Sanitize a URL slug to prevent path traversal.
     * Allows letters, numbers, dots, hyphens, underscores.
     * Rejects anything with slashes, null bytes, or dot-dot sequences.
     */
    private function sanitizeSlug(?string $slug): ?string
    {
        if ($slug === null) return null;
        // Reject null bytes, slashes, or directory traversal attempts
        if (strpos($slug, "\0") !== false
            || strpos($slug, '/') !== false
            || strpos($slug, '\\') !== false
            || strpos($slug, '..') !== false
        ) {
            return null;
        }
        // Only allow safe characters: alphanumeric, dot, hyphen, underscore
        if (!preg_match('/^[a-zA-Z0-9][a-zA-Z0-9._-]*$/', $slug)) {
            return null;
        }
        return $slug;
    }

    /**
     * Resolve the current request into a structured route.
     * Returns: ['type' => 'file'|'page', 'project' => ?, 'page' => ?, 'file' => ?]
     */
    /**
     * Resolve the current request into a structured route.
     * Returns: ['type' => 'page', 'project' => ?, 'page' => ?]
     *
     * File serving, sitemap, and robots are handled by their own
     * standalone entry points (serve.php, sitemap.php, robots.php)
     * before this ever runs.
     */
    public function route(): array
    {
        return [
            'type'    => 'page',
            'project' => $this->params['project'],
            'page'    => $this->params['page'],
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

        // Security: search all configured paths for the project directory
        $filePath = false;
        $projectPath = null;
        foreach ($this->projectsPaths as $basePath) {
            $candidate = $basePath . DIRECTORY_SEPARATOR . $projectSlug;
            if (is_dir($candidate)) {
                $projectPath = $candidate;
                $filePath = realpath($candidate . DIRECTORY_SEPARATOR . $requestedFile);
                if ($filePath) break;
            }
        }

        // Verify file is within project directory (prevent directory traversal)
        if ($filePath && $projectPath && strpos($filePath, realpath($projectPath)) === 0 && file_exists($filePath)) {
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
     * Build a clean URL for a project attachment.
     *
     *   attachmentUrl('getting-started', 'diagram.png')
     *   → /getting-started/attachments/diagram.png
     */
    public function attachmentUrl(string $project, string $filename): string
    {
        return '/' . rawurlencode($project) . '/attachments/' . rawurlencode($filename);
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
