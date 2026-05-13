<?php

/**
 * ProjectManager - Handles project and page discovery
 */

namespace Diplodocus;

use Diplodocus\Spec\SpecHandler;
use Diplodocus\Spec\FlatNumberedSpec;
use Diplodocus\Spec\FeatureDrivenSpec;

class ProjectManager
{
    private array $projectsPaths;
    private array $excludedDirs;
    private array $specCache = [];

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
                if (!is_dir($path)) continue;

                $hasMarker = is_file($path . DIRECTORY_SEPARATOR . '.diplodocus.json');
                $mdFiles = glob($path . DIRECTORY_SEPARATOR . '*.md');
                if (empty($mdFiles) && !$hasMarker) continue;

                $projects[] = [
                    'slug'      => $item,
                    'name'      => $this->formatName($item),
                    'path'      => $path,
                    'fileCount' => count($mdFiles),
                ];
            }
        }
        return $projects;
    }

    /**
     * Get all markdown pages for a project, delegating to the project's spec handler.
     */
    public function getPages(string $projectSlug): array
    {
        $projectPath = $this->getProjectPath($projectSlug);
        return $this->getSpec($projectSlug)->getPages($projectPath);
    }

    /**
     * Get the sidebar tree (mixed pages + groups). Spec-specific shape.
     */
    public function getSidebarTree(string $projectSlug): array
    {
        $projectPath = $this->getProjectPath($projectSlug);
        return $this->getSpec($projectSlug)->getSidebarTree($projectPath);
    }

    /**
     * Resolve the spec handler for a project. Reads `.diplodocus.json` from the
     * project root if present; defaults to flat-numbered otherwise.
     */
    public function getSpec(string $projectSlug): SpecHandler
    {
        if (isset($this->specCache[$projectSlug])) {
            return $this->specCache[$projectSlug];
        }

        $markerPath = $this->getProjectPath($projectSlug) . DIRECTORY_SEPARATOR . '.diplodocus.json';
        $specName = 'flat-numbered';

        if (is_file($markerPath)) {
            $decoded = json_decode((string)file_get_contents($markerPath), true);
            if (is_array($decoded) && !empty($decoded['spec'])) {
                $specName = (string)$decoded['spec'];
            }
        }

        return $this->specCache[$projectSlug] = $this->buildSpec($specName);
    }

    private function buildSpec(string $name): SpecHandler
    {
        switch ($name) {
            case 'feature-driven':
                return new FeatureDrivenSpec();
            case 'flat-numbered':
            default:
                return new FlatNumberedSpec();
        }
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
