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
            
            // Lists - collect all consecutive list items
            if (preg_match('/^(\s*)([*+-]|\d+\.)\s+(.+)/', $line, $m)) {
                $listItems = array();
                $isOrdered = is_numeric($m[2][0]);
                $i++; // advance past the first line already matched
                $listItems[] = $m[3];
                
                while ($i < count($lineArray)) {
                    $currentLine = $lineArray[$i];
                    if (empty(trim($currentLine))) {
                        $i++;
                        break; // Empty line ends the list
                    }
                    if (preg_match('/^(\s*)([*+-]|\d+\.)\s+(.+)/', $currentLine, $match)) {
                        $listItems[] = $match[3];
                        $i++;
                    } else {
                        break; // Non-list line ends the list (don't increment — outer loop handles it)
                    }
                }
                
                $tag = $isOrdered ? 'ol' : 'ul';
                $html .= '<' . $tag . '>' . "\n";
                foreach ($listItems as $item) {
                    $html .= '<li>' . $this->parseInline($item) . '</li>' . "\n";
                }
                $html .= '</' . $tag . '>' . "\n";
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
