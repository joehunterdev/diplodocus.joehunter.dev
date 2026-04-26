#!/usr/bin/env php
<?php
/**
 * Diplodocus CLI Tools
 * Scan for security issues, lint markdown, and manage spaces
 */

require_once __DIR__ . '/lib/SecurityScanner.php';
require_once __DIR__ . '/lib/MarkdownLinter.php';
require_once __DIR__ . '/src/Config.php';

class DocsCLI
{
    private $basePath;
    private $scanner;
    private $linter;

    public function __construct(string $basePath, ?string $scanPath = null)
    {
        $this->basePath = rtrim($scanPath ?? $basePath, '/\\');
        $this->scanner = new SecurityScanner();
        $this->linter = new MarkdownLinter();
    }

    /**
     * Main entry point
     */
    public function run(array $args)
    {
        $command = $args[1] ?? 'help';

        switch ($command) {
            case 'scan-security':
                $this->scanSecurity();
                break;
            case 'lint':
                $this->lintMarkdown();
                break;
            case 'check-all':
                $this->checkAll();
                break;
            case 'help':
            default:
                $this->showHelp();
        }
    }

    /**
     * Scan all markdown files for security issues
     */
    private function scanSecurity()
    {
        echo "\n🔍 Security Scan Starting...\n";
        echo str_repeat("=", 50) . "\n\n";

        $mdFiles = $this->findMarkdownFiles();
        $allIssues = [];

        foreach ($mdFiles as $file) {
            $issues = $this->scanner->scanFile($file);
            $allIssues = array_merge($allIssues, $issues);
        }

        echo $this->scanner->generateReport($allIssues);
        echo "\n✓ Scan complete. Scanned " . count($mdFiles) . " files.\n";
    }

    /**
     * Lint all markdown files
     */
    private function lintMarkdown()
    {
        echo "\n📝 Markdown Lint Starting...\n";
        echo str_repeat("=", 50) . "\n\n";

        $mdFiles = $this->findMarkdownFiles();
        $totalErrors = 0;
        $totalWarnings = 0;

        foreach ($mdFiles as $file) {
            $results = $this->linter->lintFile($file);

            if (!empty($results['errors']) || !empty($results['warnings'])) {
                echo basename($file) . ":\n";
                echo $this->linter->generateReport($results);

                $totalErrors += count($results['errors']);
                $totalWarnings += count($results['warnings']);
            }
        }

        echo "\n✓ Lint complete.\n";
        echo "  Errors: $totalErrors\n";
        echo "  Warnings: $totalWarnings\n";
    }

    /**
     * Run all checks
     */
    private function checkAll()
    {
        $this->scanSecurity();
        echo "\n";
        $this->lintMarkdown();
    }

    /**
     * Find all markdown files under the scan path, skipping hidden dirs
     * and common excluded folders (works on Windows and *nix).
     */
    private function findMarkdownFiles(): array
    {
        if (!is_dir($this->basePath)) {
            return [];
        }

        $excluded = ['node_modules', 'vendor'];
        $files = [];
        $dirIterator = new RecursiveDirectoryIterator(
            $this->basePath,
            RecursiveDirectoryIterator::SKIP_DOTS
        );
        $filter = new RecursiveCallbackFilterIterator($dirIterator, function ($current) use ($excluded) {
            $name = $current->getFilename();
            // Skip hidden folders (.git, .backup, .spaces, etc.) on any OS
            if ($name[0] === '.') return false;
            if (in_array($name, $excluded, true)) return false;
            return true;
        });
        $iterator = new RecursiveIteratorIterator($filter);
        $regex = new RegexIterator($iterator, '/^.+\.md$/i', RecursiveRegexIterator::GET_MATCH);

        foreach ($regex as $file) {
            $files[] = $file[0];
        }

        return $files;
    }

    /**
     * Show help
     */
    private function showHelp()
    {
        $help = <<<'EOF'

📚 Documentation CLI Tools
==========================

Usage: php cli.php [command]

Commands:
  scan-security   Scan all markdown files for sensitive data
  lint            Lint all markdown files for formatting issues
  check-all       Run all checks (security + lint)
  help            Show this help message

Examples:
  php cli.php scan-security
  php cli.php lint
  php cli.php check-all

EOF;
        echo $help;
    }
}

// Run CLI — scan spaces/ by default, or override with `php cli.php <cmd> <path>`
$projectRoot = __DIR__;
$scanPath = $argv[2] ?? null;

if ($scanPath === null) {
    $config = Diplodocus\Config::getInstance();
    $scanPath = $config->get('projects_path', $projectRoot . '/public_md');
}

$cli = new DocsCLI($projectRoot, $scanPath);
$cli->run($argv);
