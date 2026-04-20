<?php
/**
 * Markdown Linter
 * Validates markdown formatting
 */

class MarkdownLinter {
    
    private $errors = [];
    private $warnings = [];
    private $info = [];
    
    /**
     * Lint markdown file
     */
    public function lintFile(string $filePath): array {
        if (!file_exists($filePath)) {
            return [];
        }
        
        $content = file_get_contents($filePath);
        return $this->lint($content, $filePath);
    }
    
    /**
     * Lint markdown content
     */
    public function lint(string $content, string $filePath = 'unknown'): array {
        $this->errors = [];
        $this->warnings = [];
        $this->info = [];
        
        $lines = explode("\n", $content);
        
        // Check for common issues
        $this->checkHeadings($lines, $filePath);
        $this->checkLinks($lines, $filePath);
        $this->checkLists($lines, $filePath);
        $this->checkCodeBlocks($lines, $filePath);
        $this->checkLineLength($lines, $filePath);
        $this->checkTrailingWhitespace($lines, $filePath);
        
        return [
            'errors' => $this->errors,
            'warnings' => $this->warnings,
            'info' => $this->info,
        ];
    }
    
    /**
     * Check heading structure
     */
    private function checkHeadings(array $lines, string $filePath) {
        $lastHeadingLevel = 0;
        
        $inCodeBlock = false;
        foreach ($lines as $lineNum => $line) {
            // Skip heading checks inside fenced code blocks (### inside code
            // is not a heading, it's content).
            if (preg_match('/^```/', $line)) {
                $inCodeBlock = !$inCodeBlock;
                continue;
            }
            if ($inCodeBlock) continue;

            // Valid heading: #+ followed by a space. The regex enforces the
            // space so any match is well-formed.
            if (preg_match('/^(#{1,6})\s+(.+)$/', $line, $matches)) {
                $level = strlen($matches[1]);

                if ($level > $lastHeadingLevel + 1 && $lastHeadingLevel !== 0) {
                    $this->warnings[] = [
                        'file' => $filePath,
                        'line' => $lineNum + 1,
                        'message' => "Heading hierarchy issue: jumped from H{$lastHeadingLevel} to H{$level}",
                    ];
                }

                $lastHeadingLevel = $level;
                continue;
            }

            // Malformed heading: starts with # but no space after (e.g. "#Foo").
            if (preg_match('/^(#{1,6})[^\s#]/', $line)) {
                $this->errors[] = [
                    'file' => $filePath,
                    'line' => $lineNum + 1,
                    'message' => 'Missing space after heading marker',
                ];
            }
        }
    }
    
    /**
     * Check link formatting (skips fenced code blocks)
     */
    private function checkLinks(array $lines, string $filePath) {
        $inCodeBlock = false;
        foreach ($lines as $lineNum => $line) {
            if (preg_match('/^```/', $line)) {
                $inCodeBlock = !$inCodeBlock;
                continue;
            }
            if ($inCodeBlock) continue;

            // Check for broken link syntax
            if (preg_match_all('/\[([^\]]*)\]\(([^)]*)\)/', $line, $matches)) {
                foreach ($matches[0] as $idx => $match) {
                    $text = $matches[1][$idx];
                    $url = $matches[2][$idx];

                    if (empty($text)) {
                        $this->warnings[] = [
                            'file' => $filePath,
                            'line' => $lineNum + 1,
                            'message' => 'Link has empty text',
                        ];
                    }

                    if (empty($url)) {
                        $this->errors[] = [
                            'file' => $filePath,
                            'line' => $lineNum + 1,
                            'message' => 'Link has empty URL',
                        ];
                    }
                }
            }

            // Check for unclosed brackets, ignoring common non-link uses:
            //   task lists: "- [x]" / "- [ ]"
            //   footnote refs: "[^1]"
            $stripped = preg_replace('/^\s*-\s*\[[ xX]\]/', '', $line);
            $stripped = preg_replace('/\[\^[^\]]*\]/', '', $stripped);
            if (substr_count($stripped, '[') !== substr_count($stripped, ']')) {
                $this->warnings[] = [
                    'file' => $filePath,
                    'line' => $lineNum + 1,
                    'message' => 'Mismatched square brackets',
                ];
            }
        }
    }

    /**
     * Check list formatting (skips fenced code blocks)
     */
    private function checkLists(array $lines, string $filePath) {
        $inCodeBlock = false;
        foreach ($lines as $lineNum => $line) {
            if (preg_match('/^```/', $line)) {
                $inCodeBlock = !$inCodeBlock;
                continue;
            }
            if ($inCodeBlock) continue;

            // Check for space after list markers. Only flag `-` and `+`
            // (a lone `*` is ambiguous with *italic* / **bold**).
            // Require an alphanumeric char immediately after so that `---`
            // horizontal rules and `--flag` CLI examples don't trip.
            if (preg_match('/^[\+-][a-zA-Z0-9]/', $line)) {
                $this->warnings[] = [
                    'file' => $filePath,
                    'line' => $lineNum + 1,
                    'message' => 'Missing space after list marker',
                ];
            }
        }
    }
    
    /**
     * Check code blocks
     */
    private function checkCodeBlocks(array $lines, string $filePath) {
        $inCodeBlock = false;
        $blockStart = 0;
        
        foreach ($lines as $lineNum => $line) {
            if (preg_match('/^```(.*)/', $line)) {
                if ($inCodeBlock) {
                    $inCodeBlock = false;
                } else {
                    $inCodeBlock = true;
                    $blockStart = $lineNum + 1;
                }
            }
        }
        
        if ($inCodeBlock) {
            $this->errors[] = [
                'file' => $filePath,
                'line' => $blockStart,
                'message' => 'Unclosed code block',
            ];
        }
    }
    
    /**
     * Check for excessive line length
     */
    private function checkLineLength(array $lines, string $filePath) {
        foreach ($lines as $lineNum => $line) {
            // Skip code blocks and links
            if (preg_match('/^```|^\s{4}|\[.*\]\(.*\)/', $line)) {
                continue;
            }
            
            if (strlen($line) > 120) {
                $this->info[] = [
                    'file' => $filePath,
                    'line' => $lineNum + 1,
                    'message' => 'Line exceeds 120 characters (length: ' . strlen($line) . ')',
                ];
            }
        }
    }
    
    /**
     * Check for trailing whitespace
     */
    private function checkTrailingWhitespace(array $lines, string $filePath) {
        foreach ($lines as $lineNum => $line) {
            if ($line !== rtrim($line) && !empty($line)) {
                $this->warnings[] = [
                    'file' => $filePath,
                    'line' => $lineNum + 1,
                    'message' => 'Line has trailing whitespace',
                ];
            }
        }
    }
    
    /**
     * Generate lint report
     */
    public function generateReport(array $results): string {
        $totalErrors = count($results['errors']);
        $totalWarnings = count($results['warnings']);
        $totalInfo = count($results['info']);
        
        $report = "Markdown Lint Report\n";
        $report .= "====================\n\n";
        
        if ($totalErrors === 0 && $totalWarnings === 0 && $totalInfo === 0) {
            $report .= "✓ No issues found!\n";
            return $report;
        }
        
        if ($totalErrors > 0) {
            $report .= "ERRORS ($totalErrors):\n";
            foreach ($results['errors'] as $error) {
                $report .= "  [{$error['line']}] {$error['message']}\n";
            }
            $report .= "\n";
        }
        
        if ($totalWarnings > 0) {
            $report .= "WARNINGS ($totalWarnings):\n";
            foreach ($results['warnings'] as $warning) {
                $report .= "  [{$warning['line']}] {$warning['message']}\n";
            }
            $report .= "\n";
        }
        
        if ($totalInfo > 0) {
            $report .= "INFO ($totalInfo):\n";
            foreach ($results['info'] as $info) {
                $report .= "  [{$info['line']}] {$info['message']}\n";
            }
            $report .= "\n";
        }
        
        return $report;
    }
}
