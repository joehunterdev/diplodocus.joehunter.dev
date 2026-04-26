<?php

/**
 * ProjectManager - Handles project and page discovery
 */

namespace Diplodocus;

class ProjectManager
{
    private array $projectsPaths;
    private array $excludedDirs;

    public function __construct($projectsPath, array $excludedDirs = [])
    {
        // Accept either a single path string or an array of paths
        if (is_array($projectsPath)) {
            $this->projectsPaths = array_map(function ($p) {
                return rtrim($p, '/\\');
            }, $projectsPath);
        } else {
            $this->projectsPaths = [rtrim($projectsPath, '/\\')];
        }
        $this->excludedDirs = $excludedDirs ?: ['.git', '.backup', '.spaces', 'attachments', 'vendor', 'node_modules'];
    }

    /**
     * Get all project folders inside spaces/ that contain .md files
     */
    public function getProjects(): array
    {
        $projects = [];
        foreach ($this->projectsPaths as $projectsPath) {
            if (!is_dir($projectsPath)) continue;
            $items = scandir($projectsPath);
            foreach ($items as $item) {
                if ($item[0] === '.') continue;
                if (in_array($item, $this->excludedDirs)) continue;
                $path = $projectsPath . DIRECTORY_SEPARATOR . $item;
                if (is_dir($path)) {
                    $mdFiles = glob($path . DIRECTORY_SEPARATOR . '*.md');
                    if (!empty($mdFiles)) {
                        $projects[] = [
                            'slug'      => $item,
                            'name'      => $this->formatName($item),
                            'path'      => $path,
                            'fileCount' => count($mdFiles)
                        ];
                    }
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
        $projectPath = $this->getProjectPath($projectSlug);
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
        foreach ($this->projectsPaths as $projectsPath) {
            $path = $projectsPath . DIRECTORY_SEPARATOR . $projectSlug;
            if (is_dir($path)) {
                return $path;
            }
        }
        // Fall back to first configured path
        return $this->projectsPaths[0] . DIRECTORY_SEPARATOR . $projectSlug;
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
