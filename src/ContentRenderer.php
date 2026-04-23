<?php
/**
 * ContentRenderer - Handles markdown parsing and HTML generation
 */

namespace Diplodocus;

require_once __DIR__ . '/../lib/Parsedown.php';

class ContentRenderer
{
    private string $spacesPath;

    public function __construct(string $spacesPath)
    {
        $this->spacesPath = rtrim($spacesPath, '/\\');
    }

    /**
     * Render a markdown file to HTML
     */
    public function render(string $projectSlug, string $pageSlug): ?array
    {
        $projectPath = $this->spacesPath . DIRECTORY_SEPARATOR . $projectSlug;
        $filePath = $projectPath . DIRECTORY_SEPARATOR . $pageSlug . '.md';
        
        if (!file_exists($filePath)) {
            return null;
        }
        
        // Create parser with project-specific base path for image resolution
        $parser = new \Parsedown($projectPath);
        
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

        return [
            'title' => $title,
            'html' => $html,
            'toc' => $toc
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
