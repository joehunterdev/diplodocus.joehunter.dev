<?php

/**
 * ContentRenderer - Handles markdown parsing and HTML generation
 *
 * Hybrid approach:
 * - loadProject(): Scan project directory, return list of pages + metadata
 * - render(): Lazy-parse only the requested page, enrich with project context
 */

namespace Diplodocus;

require_once __DIR__ . '/../lib/DiplodocusMarkdown.php';

class ContentRenderer
{
    private array $projectsPaths;

    public function __construct($projectsPath)
    {
        if (is_array($projectsPath)) {
            $this->projectsPaths = array_map(function ($p) {
                return rtrim($p, '/\\');
            }, $projectsPath);
        } else {
            $this->projectsPaths = [rtrim($projectsPath, '/\\')];
        }
    }

    /**
     * Load project structure: pages list, attachments, metadata
     * Per-request (no caching) — always checks file mtime
     */
    public function loadProject(string $projectSlug): ?array
    {
        $projectPath = $this->findProjectPath($projectSlug);
        if ($projectPath === null) return null;

        // Scan markdown files
        $files = @scandir($projectPath);
        if ($files === false) return null;

        $pages = [];
        foreach ($files as $file) {
            if ($file[0] === '.' || !str_ends_with($file, '.md')) continue;
            $filePath = $projectPath . DIRECTORY_SEPARATOR . $file;
            if (!is_file($filePath)) continue;

            // Extract page slug (remove .md, optionally remove 00- prefix)
            $pageSlug = substr($file, 0, -3);
            $displayName = preg_replace('/^\d+-/', '', $pageSlug);

            $pages[] = [
                'slug' => $pageSlug,
                'filename' => $file,
                'displayName' => str_replace(['-', '_'], ' ', ucwords($displayName)),
                'mtime' => filemtime($filePath),
            ];
        }

        // Sort by filename (natural order)
        usort($pages, fn($a, $b) => strnatcmp($a['filename'], $b['filename']));

        // Scan attachments directory
        $attachments = [];
        $attachmentsPath = $projectPath . DIRECTORY_SEPARATOR . 'attachments';
        if (is_dir($attachmentsPath)) {
            $attachmentFiles = @scandir($attachmentsPath);
            if ($attachmentFiles !== false) {
                foreach ($attachmentFiles as $file) {
                    if ($file[0] !== '.' && is_file($attachmentsPath . DIRECTORY_SEPARATOR . $file)) {
                        $attachments[] = $file;
                    }
                }
            }
        }

        return [
            'slug' => $projectSlug,
            'path' => $projectPath,
            'pages' => $pages,
            'attachments' => $attachments,
            'pageCount' => count($pages),
        ];
    }

    /**
     * Find project path across configured paths
     */
    private function findProjectPath(string $projectSlug): ?string
    {
        foreach ($this->projectsPaths as $projectsPath) {
            $candidate = $projectsPath . DIRECTORY_SEPARATOR . $projectSlug;
            if (is_dir($candidate)) {
                return $candidate;
            }
        }
        return null;
    }

    /**
     * Render a markdown file to HTML with project context
     */
    public function render(string $projectSlug, string $pageSlug): ?array
    {
        // Load project structure
        $project = $this->loadProject($projectSlug);
        if ($project === null) return null;

        $filePath = $project['path'] . DIRECTORY_SEPARATOR . $pageSlug . '.md';
        if (!file_exists($filePath)) {
            return null;
        }

        // Create parser with project-specific base path for image resolution
        $parser = new \DiplodocusMarkdown($project['path']);

        $markdown = file_get_contents($filePath);
        $html = $parser->text($markdown);

        // Extract title from first H1
        $title = $this->formatPageName($pageSlug);
        if (preg_match('/<h1[^>]*>(.*?)<\/h1>/i', $html, $matches)) {
            $title = strip_tags($matches[1]);
        }

        // Extract table of contents
        $toc = $this->extractTableOfContents($html);

        // Add heading IDs for anchor navigation
        $html = $this->addHeadingIds($html);

        // Tag labelled blockquotes with data-callout="{type}"
        $html = $this->tagCallouts($html);

        // Convert GFM task list items: `[ ]` / `[x]` → checkbox inputs
        $html = $this->tagTaskLists($html);

        // Wrap tables in a scroll container for mobile
        $html = $this->wrapTables($html);

        // Build search index from title + headings
        $searchIndex = $this->buildSearchIndex($title, $toc);

        // Find page index in project
        $pageIndex = null;
        foreach ($project['pages'] as $idx => $page) {
            if ($page['slug'] === $pageSlug) {
                $pageIndex = $idx;
                break;
            }
        }

        return [
            'title' => $title,
            'html' => $html,
            'toc' => $toc,
            'searchIndex' => $searchIndex,  // NEW: for search module
            'project' => $project,       // NEW: project metadata
            'pageIndex' => $pageIndex,   // NEW: position in project
            'pageCount' => count($project['pages']),  // NEW: total pages
        ];
    }

    /**
     * Extract table of contents from HTML
     */
    private function extractTableOfContents(string $html): array
    {
        $toc = [];
        preg_match_all('/<h([2-3])[^>]*>(.*?)<\/h\1>/i', $html, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $level = (int)$match[1];
            $text = strip_tags($match[2]);
            $id = $this->slugify($text);
            $toc[] = [
                'level' => $level,
                'text' => $text,
                'id' => $id
            ];
        }

        return $toc;
    }

    /**
     * Build a global search index across all pages in a project.
     * Extracts headings from raw markdown (no full parse) for performance.
     */
    public function buildProjectSearchIndex(string $projectSlug): array
    {
        $project = $this->loadProject($projectSlug);
        if (!$project) return [];

        $index = [];
        foreach ($project['pages'] as $page) {
            $filePath = $project['path'] . DIRECTORY_SEPARATOR . $page['slug'] . '.md';
            if (!file_exists($filePath)) continue;

            $markdown = file_get_contents($filePath);
            foreach (explode("\n", $markdown) as $line) {
                if (!preg_match('/^(#{1,6})\s+(.+)$/', trim($line), $m)) continue;
                $level = strlen($m[1]);
                $text = trim($m[2]);
                $index[] = [
                    'type'      => $level === 1 ? 'title' : 'heading',
                    'text'      => $text,
                    'pageSlug'  => $page['slug'],
                    'headingId' => $this->slugify($text),
                    'level'     => $level,
                ];
            }
        }

        return $index;
    }

    /**
     * Build searchable index: page title + all headings
     * Used by search module to display results
     */
    private function buildSearchIndex(string $title, array $toc): array
    {
        $index = [];

        // Add page title as first searchable item
        $index[] = [
            'type' => 'title',
            'text' => $title,
            'headingId' => null,
            'level' => 1
        ];

        // Add all headings from table of contents
        foreach ($toc as $heading) {
            $index[] = [
                'type' => 'heading',
                'text' => $heading['text'],
                'headingId' => $heading['id'],
                'level' => $heading['level']
            ];
        }

        return $index;
    }

    /**
     * Add IDs to headings for anchor navigation
     */
    private function addHeadingIds(string $html): string
    {
        return preg_replace_callback(
            '/<h([1-6])([^>]*)>(.*?)<\/h\1>/i',
            function ($matches) {
                $level = $matches[1];
                $attrs = $matches[2];
                $text = $matches[3];
                $id = $this->slugify(strip_tags($text));

                if (strpos($attrs, 'id=') !== false) {
                    return $matches[0];
                }

                return "<h{$level} id=\"{$id}\"{$attrs}>{$text}</h{$level}>";
            },
            $html
        );
    }

    /**
     * Convert GFM-style task list items produced by Parsedown into real checkboxes.
     * Parsedown 1.7.4 renders `- [x] text` as <li>[x] text</li>.
     * We post-process those into <li class="task-list-item"><input type="checkbox" …> text</li>.
     */
    private function tagTaskLists(string $html): string
    {
        return preg_replace_callback(
            '#<li>((\[( |x|X)\])\s*(.*?))</li>#s',
            function ($m) {
                $checked = ($m[3] === 'x' || $m[3] === 'X') ? ' checked' : '';
                $text = $m[4];
                return '<li class="task-list-item"><input type="checkbox" disabled' . $checked . '> ' . $text . '</li>';
            },
            $html
        );
    }

    /**
     * Tag blockquotes whose first child is <p><strong>{Label}</strong>…
     * with data-callout="{label-lowercased}". Enables per-type CSS styling.
     */
    private function tagCallouts(string $html): string
    {
        $labels = ['note', 'tip', 'warning', 'danger', 'example', 'info', 'caution'];
        return preg_replace_callback(
            '#<blockquote([^>]*)>(\s*<p>\s*<strong>([^<]+)</strong>)#i',
            function ($m) use ($labels) {
                $existingAttrs = $m[1];
                $label = strtolower(trim($m[3]));
                if (!in_array($label, $labels, true)) {
                    return $m[0];
                }
                // Don't double-tag
                if (strpos($existingAttrs, 'data-callout') !== false) {
                    return $m[0];
                }
                return '<blockquote' . $existingAttrs . ' data-callout="' . $label . '">' . $m[2];
            },
            $html
        );
    }

    /**
     * Wrap bare <table> elements in a scroll container so wide tables
     * scroll horizontally on mobile instead of breaking the layout.
     */
    private function wrapTables(string $html): string
    {
        return preg_replace(
            '#(?<!<div class="table-wrap">)(<table[\s>])#',
            '<div class="table-wrap">$1',
            preg_replace(
                '#(<\/table>)(?!\s*<\/div>)#',
                '$1</div>',
                $html
            )
        );
    }

    /**
     * Generate URL-friendly slug
     */
    private function slugify(string $text): string
    {
        $text = preg_replace('/[^\w\s-]/u', '', $text);
        $text = preg_replace('/[\s_]+/', '-', $text);
        return strtolower(trim($text, '-'));
    }

    /**
     * Format page slug to display name
     */
    private function formatPageName(string $name): string
    {
        $name = preg_replace('/^\d+-/', '', $name);
        $name = str_replace(['-', '_'], ' ', $name);
        return ucwords($name);
    }
}
