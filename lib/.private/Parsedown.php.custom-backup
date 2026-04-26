<?php
# Parsedown
# https://parsedown.org
# Simplified version for documentation with proper block-level handling

class Parsedown
{
    const VERSION = '1.7.4';
    
    public $breaksEnabled = false;
    public $safeMode = false;
    
    protected $DefinitionData = array();
    protected $basePath = '';
    
    public function __construct($basePath = '')
    {
        $this->basePath = $basePath;
    }

    public function text($text)
    {
        $text = str_replace(array("\r\n", "\r"), "\n", $text);
        $text = trim($text, "\n");
        $lineArray = explode("\n", $text);
        
        return $this->parseLines($lineArray);
    }

    public function line($text)
    {
        return $this->parseInline($text);
    }
    
    protected function resolvePath($path)
    {
        // If path is absolute or starts with http/https, return as-is
        if (preg_match('#^(https?://|/)#', $path)) {
            return $path;
        }
        
        // Clean up path (remove ./ prefix)
        $path = preg_replace('#^\./#', '', $path);
        
        if ($this->basePath) {
            $projectName = basename($this->basePath);
            
            // If no directory prefix, assume it lives in attachments/
            if (strpos($path, '/') === false) {
                $path = 'attachments/' . $path;
            }
            
            // Build the file serving URL — do NOT urlencode the slash in the path
            return '?project=' . urlencode($projectName) . '&file=' . $path;
        }
        
        // Return original path if no basePath
        return $path;
    }

    protected function parseLines($lineArray)
    {
        $html = '';
        $i = 0;
        $inCodeBlock = false;
        $codeContent = '';
        $codeLang = '';
        
        while ($i < count($lineArray)) {
            $line = $lineArray[$i];
            
            // Code blocks
            if (preg_match('/^```(\w*)/', $line, $m)) {
                if ($inCodeBlock) {
                    $html .= '<pre><code' . ($codeLang ? ' class="language-' . htmlspecialchars($codeLang) . '"' : '') . '>' 
                           . htmlspecialchars($codeContent) . '</code></pre>' . "\n";
                    $inCodeBlock = false;
                    $codeContent = '';
                } else {
                    $inCodeBlock = true;
                    $codeLang = $m[1] ?? '';
                }
                $i++;
                continue;
            }
            
            if ($inCodeBlock) {
                $codeContent .= ($codeContent ? "\n" : '') . $line;
                $i++;
                continue;
            }
            
            // Empty lines - skip
            if (empty(trim($line))) {
                $i++;
                continue;
            }
            
            // Headings
            if (preg_match('/^(#{1,6})\s+(.+?)(?:\s+#+)?$/', $line, $m)) {
                $level = strlen($m[1]);
                $text = $m[2];
                $html .= '<h' . $level . '>' . $this->parseInline($text) . '</h' . $level . '>' . "\n";
                $i++;
                continue;
            }
            
            // Horizontal rule
            if (preg_match('/^(\*|-|_){3,}\s*$/', $line)) {
                $html .= '<hr />' . "\n";
                $i++;
                continue;
            }
            
            // Lists - collect all consecutive list items with indent tracking
            if (preg_match('/^(\s*)([*+-]|\d+\.)\s+(.+)/', $line, $m)) {
                $listItems = array();
                $firstIndent = strlen(str_replace("\t", '    ', $m[1]));
                $firstOrdered = is_numeric($m[2][0]);

                // first line
                $firstItem = $this->parseListItemLine($m);
                $listItems[] = $firstItem;
                $i++;

                while ($i < count($lineArray)) {
                    $currentLine = $lineArray[$i];
                    if (empty(trim($currentLine))) {
                        $i++;
                        break; // Empty line ends the list
                    }
                    if (preg_match('/^(\s*)([*+-]|\d+\.)\s+(.+)/', $currentLine, $match)) {
                        $listItems[] = $this->parseListItemLine($match);
                        $i++;
                    } else {
                        break;
                    }
                }

                $html .= $this->renderListItems($listItems, $firstOrdered, $firstIndent);
                continue;
            }
            
            // Blockquotes - collect all consecutive blockquote lines
            if (preg_match('/^>\s*(.*)/', $line, $m)) {
                $quoteLines = array();
                $i++; // advance past the first line already matched
                if ($m[1] !== '') $quoteLines[] = $m[1];
                
                while ($i < count($lineArray)) {
                    $currentLine = $lineArray[$i];
                    if (empty(trim($currentLine))) {
                        $i++;
                        break;
                    }
                    if (preg_match('/^>\s*(.*)/', $currentLine, $match)) {
                        $quoteLines[] = $match[1];
                        $i++;
                    } else {
                        break; // Non-blockquote line ends the quote (don't increment — outer loop handles it)
                    }
                }
                $html .= '<blockquote>' . "\n" . '<p>' . implode('</p>' . "\n" . '<p>', array_map([$this, 'parseInline'], $quoteLines)) . '</p>' . "\n" . '</blockquote>' . "\n";
                continue;
            }
            
            // Tables
            if (strpos($line, '|') !== false && preg_match('/^\|.+\|$/', $line)) {
                $tableRows = array();
                while ($i < count($lineArray) && strpos($lineArray[$i], '|') !== false) {
                    $tableRows[] = $lineArray[$i];
                    $i++;
                }
                
                if (count($tableRows) > 0) {
                    $html .= '<table>' . "\n";
                    $inBody = false;
                    foreach ($tableRows as $idx => $row) {
                        // Skip separator row (|---|---| or | --- | --- | or |:--:|:--:|)
                        // Match rows that contain only |, -, :, and whitespace
                        if (preg_match('/^\|[\s\-:\|]+\|?$/', trim($row)) && preg_match('/\-{2,}/', $row)) {
                            continue;
                        }
                        
                        $cells = array_map('trim', explode('|', trim($row, '|')));
                        
                        if ($idx === 0) {
                            // Header row
                            $html .= '<thead><tr>';
                            foreach ($cells as $cell) {
                                $html .= '<th>' . $this->parseInline($cell) . '</th>';
                            }
                            $html .= '</tr></thead>' . "\n" . '<tbody>' . "\n";
                            $inBody = true;
                        } else {
                            // Body row
                            $html .= '<tr>';
                            foreach ($cells as $cell) {
                                $html .= '<td>' . $this->parseInline($cell) . '</td>';
                            }
                            $html .= '</tr>' . "\n";
                        }
                    }
                    if ($inBody) {
                        $html .= '</tbody>' . "\n";
                    }
                    $html .= '</table>' . "\n";
                }
                continue;
            }
            
            // Paragraphs - collect consecutive non-empty lines
            $paragraphLines = array();
            $paragraphStart = $i;
            while ($i < count($lineArray)) {
                $currentLine = $lineArray[$i];
                
                if (empty(trim($currentLine))) {
                    $i++;
                    break; // Empty line ends paragraph
                }
                
                // Stop if we hit a block element
                if (preg_match('/^(#{1,6}\s|```|>|[*+-]\s|\d+\.\s|[\*\-_]{3}|\|)/', $currentLine)) {
                    break;
                }
                
                $paragraphLines[] = $currentLine;
                $i++;
            }
            
            if (!empty($paragraphLines)) {
                $paragraphText = implode(' ', $paragraphLines);
                $html .= '<p>' . $this->parseInline($paragraphText) . '</p>' . "\n";
            } elseif ($i === $paragraphStart) {
                // Safety: no block pattern matched and no paragraph collected — advance to prevent infinite loop
                $i++;
            }
        }
        
        return $html;
    }

    /**
     * Normalise a matched list line into an item descriptor.
     */
    protected function parseListItemLine($match)
    {
        $indent = strlen(str_replace("\t", '    ', $match[1]));
        $ordered = is_numeric($match[2][0]);
        $text = $match[3];
        $task = null;
        if (preg_match('/^\[([ xX])\]\s+(.*)/', $text, $tm)) {
            $task = ($tm[1] === 'x' || $tm[1] === 'X');
            $text = $tm[2];
        }
        return array(
            'indent'  => $indent,
            'ordered' => $ordered,
            'text'    => $text,
            'task'    => $task,
        );
    }

    /**
     * Render a flat array of list items as possibly-nested <ul>/<ol> using
     * indent levels. Every time an item's indent exceeds the baseline, the
     * children are collected and rendered recursively.
     */
    protected function renderListItems($items, $ordered, $baselineIndent)
    {
        if (empty($items)) return '';
        $tag = $ordered ? 'ol' : 'ul';
        $html = '<' . $tag . '>' . "\n";
        $n = count($items);
        $i = 0;
        while ($i < $n) {
            $item = $items[$i];
            // Items with indent == baseline are siblings at this level.
            if ($item['indent'] < $baselineIndent) {
                // Shouldn't happen with well-formed input; skip.
                $i++;
                continue;
            }
            if ($item['indent'] > $baselineIndent) {
                // Defensive: collect consecutive deeper items and recurse.
                $child = array();
                while ($i < $n && $items[$i]['indent'] > $baselineIndent) {
                    $child[] = $items[$i];
                    $i++;
                }
                $html .= $this->renderListItems($child, $child[0]['ordered'], $child[0]['indent']);
                continue;
            }

            // Build this <li> content, absorbing any deeper-indented children.
            $liContent = $this->parseInline($item['text']);
            $liClass = '';
            if ($item['task'] !== null) {
                $checked = $item['task'] ? ' checked' : '';
                $liContent = '<input type="checkbox" disabled' . $checked . '> ' . $liContent;
                $liClass = ' class="task-list-item"';
            }

            // Collect children of THIS item (everything after it whose indent is greater, up to the next sibling)
            $children = array();
            $j = $i + 1;
            while ($j < $n && $items[$j]['indent'] > $baselineIndent) {
                $children[] = $items[$j];
                $j++;
            }
            $childrenHtml = '';
            if (!empty($children)) {
                $childrenHtml = $this->renderListItems($children, $children[0]['ordered'], $children[0]['indent']);
            }

            $html .= '<li' . $liClass . '>' . $liContent . $childrenHtml . '</li>' . "\n";
            $i = $j;
        }
        $html .= '</' . $tag . '>' . "\n";
        return $html;
    }

    /**
     * Parse a table separator row like `|:---|:---:|---:|` into per-column alignment.
     * Returns array of 'left' | 'center' | 'right' | null per column.
     */
    protected function parseTableAlignment($row)
    {
        $cells = array_map('trim', explode('|', trim($row, '|')));
        $out = array();
        foreach ($cells as $cell) {
            $l = strlen($cell) > 0 && $cell[0] === ':';
            $r = strlen($cell) > 0 && substr($cell, -1) === ':';
            if ($l && $r) $out[] = 'center';
            elseif ($r)   $out[] = 'right';
            elseif ($l)   $out[] = 'left';
            else          $out[] = null;
        }
        return $out;
    }

    protected function parseInline($text)
    {
        // Images - must be processed BEFORE links to handle ![alt](url) correctly
        $text = preg_replace_callback('/!\[([^\]]*)\]\(([^)]+)\)/', function($m) {
            $alt = htmlspecialchars($m[1]);
            $src = $this->resolvePath($m[2]);
            return '<img src="' . htmlspecialchars($src) . '" alt="' . $alt . '" class="prose-img" />';
        }, $text);
        
        // Links
        $text = preg_replace_callback('/\[([^\]]+)\]\(([^)]+)\)/', function($m) {
            $href = $m[2];

            if ($this->basePath) {
                $projectName = basename($this->basePath);

                // Internal .md link → page navigation URL
                // Matches: 07-realtime-considerations.md or ./06-standards.md
                if (preg_match('#^(?:\./)?(\d+-[\w-]+)\.md$#', $href, $md)) {
                    $href = '?project=' . urlencode($projectName) . '&page=' . urlencode($md[1]);
                }
                // Attachment link → scroll to attachments section and open in lightbox
                elseif (preg_match('#^attachments/(.+)$#', $href, $att)) {
                    $filename = $att[1];
                    return '<a href="#attachments-section" data-open-attachment="' . htmlspecialchars($filename) . '">' . htmlspecialchars($m[1]) . '</a>';
                }
                // Bare filename with known extension → same as above
                elseif (preg_match('#^[\w.-]+\.(png|jpg|jpeg|gif|svg|webp|pdf|csv|json)$#i', $href)) {
                    return '<a href="#attachments-section" data-open-attachment="' . htmlspecialchars($href) . '">' . htmlspecialchars($m[1]) . '</a>';
                }
            }

            return '<a href="' . htmlspecialchars($href) . '">' . htmlspecialchars($m[1]) . '</a>';
        }, $text);
        
        // Code
        $text = preg_replace_callback('/`([^`]+)`/', function($m) {
            return '<code>' . htmlspecialchars($m[1]) . '</code>';
        }, $text);
        
        // Strong
        $text = preg_replace_callback('/\*\*([^*]+)\*\*|__([^_]+)__/', function($m) {
            $content = $m[1] ?? $m[2];
            return '<strong>' . htmlspecialchars($content) . '</strong>';
        }, $text);
        
        // Emphasis
        $text = preg_replace_callback('/\*([^*]+)\*|_([^_]+)_/', function($m) {
            $content = $m[1] ?? $m[2];
            return '<em>' . htmlspecialchars($content) . '</em>';
        }, $text);
        
        return $text;
    }
}
