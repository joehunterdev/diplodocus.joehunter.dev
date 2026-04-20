<?php
/**
 * Router - Handles URL routing and file serving
 */

namespace Diplodocus;

class Router
{
    private string $spacesPath;
    private array $params = [];

    public function __construct(string $spacesPath)
    {
        $this->spacesPath = rtrim($spacesPath, '/\\');
        $this->parseRequest();
    }

    /**
     * Parse the current request
     */
    private function parseRequest(): void
    {
        $this->params = [
            'project' => $_GET['project'] ?? null,
            'page' => $_GET['page'] ?? null,
            'file' => $_GET['file'] ?? null,
            'action' => $_GET['action'] ?? null,
        ];
    }

    /**
     * Resolve the current request into a structured route.
     * Returns: ['type' => 'file'|'page', 'project' => ?, 'page' => ?, 'file' => ?]
     */
    public function route(): array
    {
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
        $projectPath = $this->spacesPath . DIRECTORY_SEPARATOR . $projectSlug;
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
     * Build a URL with parameters
     */
    public function url(array $params = []): string
    {
        if (empty($params)) {
            return '?';
        }
        return '?' . http_build_query($params);
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
