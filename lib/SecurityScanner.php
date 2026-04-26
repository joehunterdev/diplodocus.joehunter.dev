<?php

/**
 * Security Scanner for Markdown Documentation
 * Detects sensitive data patterns
 */

class SecurityScanner
{

    private $patterns = [
        'API_KEY' => [
            '/sk_live_[a-zA-Z0-9]{24,}/',  // Stripe
            '/pk_live_[a-zA-Z0-9]{24,}/',  // Stripe
            '/AKIA[A-Z0-9]{16,}/',          // AWS
            '/ghp_[a-zA-Z0-9_]{36,}/',      // GitHub
            '/glpat-[a-zA-Z0-9\-_]{20,}/',  // GitLab
        ],
        'PASSWORD' => [
            '/password\s*[:=]\s*["\']?([a-zA-Z0-9!@#$%^&*()]{8,})["\']?/i',
            '/pwd\s*[:=]\s*["\']?([a-zA-Z0-9!@#$%^&*()]{8,})["\']?/i',
        ],
        'DATABASE' => [
            '/mysql:\/\/[^@]+@[^\/]+\/\w+/i',
            '/postgresql:\/\/[^@]+@[^\/]+\/\w+/i',
        ],
        'PRIVATE_KEY' => [
            '/-----BEGIN RSA PRIVATE KEY-----/',
            '/-----BEGIN PRIVATE KEY-----/',
            '/-----BEGIN EC PRIVATE KEY-----/',
        ],
        'OAUTH_TOKEN' => [
            '/access_token\s*[:=]\s*["\']?([a-zA-Z0-9\-_.]{20,})["\']?/i',
            '/refresh_token\s*[:=]\s*["\']?([a-zA-Z0-9\-_.]{20,})["\']?/i',
        ],
        'CREDIT_CARD' => [
            '/\b[0-9]{4}[\s\-]?[0-9]{4}[\s\-]?[0-9]{4}[\s\-]?[0-9]{4}\b/',
        ],
    ];

    /**
     * Scan file for sensitive data
     */
    public function scanFile(string $filePath): array
    {
        if (!file_exists($filePath)) {
            return [];
        }

        $content = file_get_contents($filePath);
        return $this->scan($content, $filePath);
    }

    /**
     * Scan content for sensitive data
     */
    public function scan(string $content, string $filePath = 'unknown'): array
    {
        $issues = [];

        foreach ($this->patterns as $type => $regexes) {
            foreach ($regexes as $regex) {
                if (preg_match_all($regex, $content, $matches, PREG_OFFSET_CAPTURE)) {
                    foreach ($matches[0] as $match) {
                        $line = $this->getLineNumber($content, $match[1]);

                        // Skip placeholder data
                        if ($this->isPlaceholder($match[0])) {
                            continue;
                        }

                        $issues[] = [
                            'file' => $filePath,
                            'type' => $type,
                            'line' => $line,
                            'match' => substr($match[0], 0, 50),
                            'severity' => $this->getSeverity($type),
                        ];
                    }
                }
            }
        }

        return $issues;
    }

    /**
     * Get line number from content and offset
     */
    private function getLineNumber(string $content, int $offset): int
    {
        return substr_count($content, "\n", 0, $offset) + 1;
    }

    /**
     * Check if match is placeholder data
     */
    private function isPlaceholder(string $match): bool
    {
        $lower = strtolower($match);

        // Check standard placeholders
        $placeholders = [
            'example',
            'test',
            '0000-0000-0000-0000',
            '1111-1111-1111-1111',
        ];

        foreach ($placeholders as $placeholder) {
            if (stripos($lower, $placeholder) !== false) {
                return true;
            }
        }

        // Ignore TypeScript/code type interactions like: password: string, password: string)
        // These appear in function signatures, interfaces, and type definitions
        if (preg_match('/(?:password|pwd)\s*:\s*string/i', $match)) {
            return true;
        }

        // Ignore visual/UI placeholder patterns like: Password: [***]
        if (preg_match('/password\s*:\s*\[[\*\s]+\]/i', $match)) {
            return true;
        }

        return false;
    }

    /**
     * Get severity level
     */
    private function getSeverity(string $type): string
    {
        $severities = [
            'PRIVATE_KEY' => 'critical',
            'API_KEY' => 'high',
            'PASSWORD' => 'high',
            'DATABASE' => 'high',
            'OAUTH_TOKEN' => 'high',
            'CREDIT_CARD' => 'critical',
        ];

        return $severities[$type] ?? 'medium';
    }

    /**
     * Generate security report
     */
    public function generateReport(array $issues): string
    {
        if (empty($issues)) {
            return "✓ No sensitive data detected.\n";
        }

        $report = "⚠ Security Scan Report\n";
        $report .= "======================\n\n";

        $bySeverity = [];
        foreach ($issues as $issue) {
            $severity = $issue['severity'];
            if (!isset($bySeverity[$severity])) {
                $bySeverity[$severity] = [];
            }
            $bySeverity[$severity][] = $issue;
        }

        foreach (['critical', 'high', 'medium', 'low'] as $severity) {
            if (isset($bySeverity[$severity])) {
                $report .= strtoupper($severity) . " (" . count($bySeverity[$severity]) . " issues):\n";
                foreach ($bySeverity[$severity] as $issue) {
                    $report .= "  - {$issue['file']}:{$issue['line']} ({$issue['type']})\n";
                }
                $report .= "\n";
            }
        }

        return $report;
    }
}
