<?php

/**
 * DiplodocusMarkdown
 * ------------------
 * Thin subclass over vendored Parsedown 1.7.4 that adds:
 *   - Relative .md → /project/page clean URL rewriting
 *   - .md#anchor → /project/page#anchor (cross-page anchors)
 *   - attachments/file.ext → ?project=X&file=attachments/file.ext
 *   - Bare filename.ext (with known extension) → attachments lightbox link
 *   - Image src rewriting for the same paths
 *
 * All other markdown handling is delegated to Parsedown 1.7.4.
 */

require_once __DIR__ . '/Parsedown.php';

class DiplodocusMarkdown extends Parsedown
{
    /** @var string Absolute path to the project (e.g. /…/public/getting-started). */
    protected $basePath = '';

    /** @var bool Set to true while inlineImage is running so inlineLink skips href rewriting. */
    private $processingImage = false;

    /** @var string[] Extensions that, when used as a bare filename, are treated as attachments. */
    protected $attachmentExtensions = [
        'png',
        'jpg',
        'jpeg',
        'gif',
        'svg',
        'webp',
        'pdf',
        'csv',
        'json',
        'xml',
        'doc',
        'docx',
        'xls',
        'xlsx',
        'ppt',
        'pptx',
    ];

    public function __construct($basePath = '')
    {
        $this->basePath = $basePath;
    }

    // ── Image src rewriting ───────────────────────────────────────────
    protected function inlineImage($Excerpt)
    {
        $this->processingImage = true;
        $Inline = parent::inlineImage($Excerpt);
        $this->processingImage = false;
        if (!isset($Inline['element']['attributes']['src']) || !$Inline['element']['attributes']['src']) {
            return $Inline;
        }
        $Inline['element']['attributes']['src'] = $this->resolveAssetPath(
            $Inline['element']['attributes']['src']
        );
        $existing = isset($Inline['element']['attributes']['class'])
            ? $Inline['element']['attributes']['class'] . ' '
            : '';
        $Inline['element']['attributes']['class'] = $existing . 'prose-img';
        return $Inline;
    }

    // ── Link href rewriting ───────────────────────────────────────────
    protected function inlineLink($Excerpt)
    {
        $Inline = parent::inlineLink($Excerpt);
        // When called during image processing, skip href rewriting — the src
        // is handled separately by inlineImage → resolveAssetPath.
        if ($this->processingImage) {
            return $Inline;
        }
        if (!isset($Inline['element']['attributes']['href'])) {
            return $Inline;
        }

        $href = $Inline['element']['attributes']['href'];
        $rewritten = $this->rewriteLinkHref($href);

        if (is_array($rewritten)) {
            $Inline['element']['attributes']['href']              = $rewritten['href'];
            $Inline['element']['attributes']['data-open-attachment'] = $rewritten['filename'];
        } else {
            $Inline['element']['attributes']['href'] = $rewritten;
        }
        return $Inline;
    }

    /**
     * Rewrite a markdown link's href to its final URL.
     *
     * Returns a string href, or an array { href, filename } for attachment lightbox links.
     *
     * @param  string $href
     * @return string|array
     */
    protected function rewriteLinkHref($href)
    {
        // Pass-through: absolute URLs and root-relative links
        if (preg_match('#^(https?://|//|/|mailto:|tel:)#i', $href)) {
            return $href;
        }
        // Pass-through: anchor-only links on the same page
        if (strlen($href) > 0 && $href[0] === '#') {
            return $href;
        }

        $project = $this->basePath ? basename($this->basePath) : null;

        // .md or .md#anchor → clean page URL (with optional fragment)
        // Matches: 07-page.md, ./06-other.md, 03-folder-structure.md#callouts
        if ($project && preg_match('~^(?:\./)?([\d]+-[\w-]+)\.md(#[\w-]+)?$~', $href, $m)) {
            $url = '/' . urlencode($project) . '/' . urlencode($m[1]);
            if (!empty($m[2])) {
                $url .= $m[2];
            }
            return $url;
        }

        // attachments/filename.ext → lightbox attachment
        if ($project && preg_match('#^attachments/(.+)$#', $href, $m)) {
            return [
                'href'     => '#attachments-section',
                'filename' => $m[1],
            ];
        }

        // Bare filename with known extension → lightbox attachment
        if (
            $project && preg_match('#^[\w.-]+\.([a-z0-9]+)$#i', $href, $m)
            && in_array(strtolower($m[1]), $this->attachmentExtensions, true)
        ) {
            return [
                'href'     => '#attachments-section',
                'filename' => $href,
            ];
        }

        // Cross-space link: ../my-other-space/01-page.md
        if ($project && preg_match('~^\.\./([\w.-]+)/([\d]+-[\w-]+)\.md(#[\w-]+)?$~', $href, $m)) {
            $url = '/' . urlencode($m[1]) . '/' . urlencode($m[2]);
            if (!empty($m[3])) {
                $url .= $m[3];
            }
            return $url;
        }

        // Anything else: pass through unchanged
        return $href;
    }

    /**
     * Resolve an image src to its served URL.
     * Bare filenames are assumed to live in attachments/.
     *
     * @param  string $path
     * @return string
     */
    protected function resolveAssetPath($path)
    {
        // Nothing to rewrite
        if (!$path) {
            return $path;
        }

        if (preg_match('#^(https?://|//|data:|/)#i', $path)) {
            return $path;
        }
        $path = preg_replace('#^\./#', '', $path);
        if (!$this->basePath) {
            return $path;
        }

        $project = basename($this->basePath);
        // No directory prefix → assume attachments/
        if (strpos($path, '/') === false) {
            $path = 'attachments/' . $path;
        }
        // Build root-relative clean URL — works from any page regardless of clean URL depth
        if (preg_match('#^attachments/(.+)$#', $path, $m)) {
            return '/' . urlencode($project) . '/attachments/' . rawurlencode($m[1]);
        }
        return '/' . urlencode($project) . '/' . $path;
    }
}
