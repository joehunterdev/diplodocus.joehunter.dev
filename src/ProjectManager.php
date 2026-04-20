<?php
/**
 * ProjectManager - Handles project and page discovery
 */

namespace Diplodocus;

class ProjectManager
{
    private string $spacesPath;
    private array $excludedDirs;

    public function __construct(string $spacesPath, array $excludedDirs = [])
    {
        $this->spacesPath = rtrim($spacesPath, '/\\');
        $this->excludedDirs = $excludedDirs ?: ['.git', '.backup', '.spaces', 'attachments', 'vendor', 'node_modules'];
    }

    /**
     * Get all project folders inside spaces/ that contain .md files
     */
    public function getProjects(): array
    {
        if (!is_dir($this->spacesPath)) {
            return [];
        }

        $projects = [];
        $items = scandir($this->spacesPath);

        foreach ($items as $item) {
            if ($item[0] === '.') continue;
            if (in_array($item, $this->excludedDirs)) continue;

            $path = $this->spacesPath . DIRECTORY_SEPARATOR . $item;
            if (is_dir($path)) {
                $mdFiles = glob($path . DIRECTORY_SEPARATOR . '*.md');
                if (!empty($mdFiles)) {
                    $projects[] = [
                        'slug' => $item,
                        'name' => $this->formatName($item),
                        'path' => $path,
                        'fileCount' => count($mdFiles)
                    ];
                }
            }
        }

        return $projects;
    }
    
    /**
     * Get all markdown pages for a project
     */
    public function getPages(string $projectSlug): array
    {
        $projectPath = $this->spacesPath . DIRECTORY_SEPARATOR . $projectSlug;
        if (!is_dir($projectPath)) return [];
        
        $files = glob($projectPath . DIRECTORY_SEPARATOR . '*.md');
        $pages = [];
        
        foreach ($files as $file) {
            $filename = basename($file, '.md');
            if (preg_match('/^(\d+)-(.+)$/', $filename, $matches)) {
                $pages[] = [
                    'order' => (int)$matches[1],
                    'slug' => $filename,
                    'name' => $this->formatName($matches[2]),
                    'path' => $file
                ];
            }
        }
        
        usort($pages, fn($a, $b) => $a['order'] <=> $b['order']);
        
        return $pages;
    }
    
    /**
     * Get project info by slug
     */
    public function getProject(string $slug): ?array
    {
        $projects = $this->getProjects();
        foreach ($projects as $project) {
            if ($project['slug'] === $slug) {
                return $project;
            }
        }
        return null;
    }
    
    /**
     * Get page info by slug
     */
    public function getPage(string $projectSlug, string $pageSlug): ?array
    {
        $pages = $this->getPages($projectSlug);
        foreach ($pages as $page) {
            if ($page['slug'] === $pageSlug) {
                return $page;
            }
        }
        return null;
    }
    
    /**
     * Get previous and next pages for navigation
     */
    public function getPageNavigation(string $projectSlug, string $pageSlug): array
    {
        $pages = $this->getPages($projectSlug);
        $prev = null;
        $next = null;
        
        foreach ($pages as $i => $page) {
            if ($page['slug'] === $pageSlug) {
                $prev = $pages[$i - 1] ?? null;
                $next = $pages[$i + 1] ?? null;
                break;
            }
        }
        
        return ['prev' => $prev, 'next' => $next];
    }
    
    /**
     * Get the project path
     */
    public function getProjectPath(string $projectSlug): string
    {
        return $this->spacesPath . DIRECTORY_SEPARATOR . $projectSlug;
    }
    
    /**
     * Format a slug into a display name
     */
    private function formatName(string $name): string
    {
        $name = str_replace(['.', '-', '_'], ' ', $name);
        return ucwords($name);
    }
}
