<?php
/**
 * Validator - Handles security scanning and markdown linting
 */

namespace Diplodocus;

require_once __DIR__ . '/../lib/SecurityScanner.php';
require_once __DIR__ . '/../lib/MarkdownLinter.php';

class Validator
{
    private string $basePath;
    private \SecurityScanner $scanner;
    private \MarkdownLinter $linter;
    private array $excludedDirs = ['lib', 'assets', 'templates', '.spaces'];
    
    public function __construct(string $basePath)
    {
        $this->basePath = rtrim($basePath, '/\\');
        $this->scanner = new \SecurityScanner();
        $this->linter = new \MarkdownLinter();
    }
    
    /**
     * Validate all markdown files
     */
    public function validateAll(): array
    {
        $results = [
            'security' => [],
            'lint' => [],
            'stats' => [
                'files' => 0,
                'issues' => 0,
            ]
        ];
        
        $mdFiles = $this->findAllMarkdownFiles();
        $results['stats']['files'] = count($mdFiles);
        
        foreach ($mdFiles as $file) {
            $securityIssues = $this->scanner->scanFile($file);
            $lintResults = $this->linter->lintFile($file);
            
            if (!empty($securityIssues)) {
                $results['security'] = array_merge($results['security'], $securityIssues);
            }
            
            $allLintIssues = array_merge(
                $lintResults['errors'] ?? [],
                $lintResults['warnings'] ?? []
            );
            
            if (!empty($allLintIssues)) {
                $results['lint'] = array_merge($results['lint'], $allLintIssues);
            }
        }
        
        $results['stats']['issues'] = count($results['security']) + count($results['lint']);
        
        return $results;
    }
    
    /**
     * Check if there are security issues (blocking)
     */
    public function hasSecurityIssues(): bool
    {
        $results = $this->validateAll();
        return !empty($results['security']);
    }
    
    /**
     * Check if there are lint issues (non-blocking)
     */
    public function hasLintIssues(): bool
    {
        $results = $this->validateAll();
        return !empty($results['lint']);
    }
    
    /**
     * Find all markdown files recursively
     */
    private function findAllMarkdownFiles(): array
    {
        $files = [];
        $pattern = $this->basePath . DIRECTORY_SEPARATOR . '*' . DIRECTORY_SEPARATOR . '*.md';
        
        foreach (glob($pattern) as $file) {
            // Skip excluded directories
            $dir = basename(dirname($file));
            if (!in_array($dir, $this->excludedDirs)) {
                $files[] = $file;
            }
        }
        
        return $files;
    }
}
