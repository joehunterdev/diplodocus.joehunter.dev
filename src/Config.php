<?php

/**
 * Config - Centralized configuration
 */

namespace Diplodocus;

class Config
{
    private static ?Config $instance = null;
    private array $config = [];

    private function __construct()
    {
        $this->loadDefaults();
    }

    /**
     * Get singleton instance
     */
    public static function getInstance(): Config
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Load default configuration
     */
    private function loadDefaults(): void
    {
        $basePath = dirname(__DIR__);

        $this->config = [
            // Paths
            'base_path' => $basePath,
            'spaces_path' => $basePath . '/spaces',   // ← Projects live inside spaces/
            'lib_path' => $basePath . '/lib',
            'assets_path' => $basePath . '/assets',
            'templates_path' => $basePath . '/templates',

            // Folder names to ignore when scanning spaces_path for projects
            'excluded_dirs' => ['.git', '.backup', '.spaces', 'attachments', 'vendor', 'node_modules'],

            // Allowed file types for serving
            'allowed_file_types' => [
                'png',
                'jpg',
                'jpeg',
                'gif',
                'svg',
                'webp',
                'css',
                'js',
                'pdf',
                'doc',
                'docx',
                'json',
                'xml',
                'csv',
                'html',
                'htm'
            ],

            // MIME types mapping
            'mime_types' => [
                'png' => 'image/png',
                'jpg' => 'image/jpeg',
                'jpeg' => 'image/jpeg',
                'gif' => 'image/gif',
                'svg' => 'image/svg+xml',
                'webp' => 'image/webp',
                'css' => 'text/css',
                'js' => 'application/javascript',
                'pdf' => 'application/pdf',
                'json' => 'application/json',
                'xml' => 'application/xml',
                'csv' => 'text/csv',
                'html' => 'text/html',
                'htm' => 'text/html'
            ],

            // Branding (can be overridden via config.php)
            'app_name' => 'Diplodocus',
            'logo_url' => '',

            // Stylesheets (theme.css MUST be first — its vars feed the rest)
            'stylesheets' => [
                'assets/css/theme.css',
                'assets/css/tailwind.min.css',
                'assets/css/highlight-dark.min.css',
                'assets/css/diplodocus.css',
            ],

            // Scripts loaded at the end of <body>
            'scripts' => [
                'assets/js/highlight.min.js',
                'assets/js/app.js',
            ],

            // Theme settings
            'default_theme' => 'light',
            'enable_dark_mode' => true,

            // Security
            'block_on_security_issues' => true,
            'block_on_lint_issues' => false,

            // Error handling
            // Set 'debug' => true in config.php to show full error details
            'debug' => false,
            // Set 'error_log' to an absolute path to log errors to a file
            'error_log' => '',

            // SEO
            'site_url'         => '',
            'site_description' => '',
            'og_image'         => '/example.png',
            'author_url'       => '',
            'github_url'       => '',
            // Spaces listed here get noindex + are excluded from sitemap
            'private_spaces'   => [],
        ];

        // Allow local overrides from project root
        $this->loadFromFile($basePath . '/config.php');
        $this->loadFromFile($basePath . '/config.local.php');
    }

    /**
     * Get a configuration value
     */
    public function get(string $key, $default = null)
    {
        return $this->config[$key] ?? $default;
    }

    /**
     * Set a configuration value
     */
    public function set(string $key, $value): self
    {
        $this->config[$key] = $value;
        return $this;
    }

    /**
     * Check if a configuration key exists
     */
    public function has(string $key): bool
    {
        return isset($this->config[$key]);
    }

    /**
     * Get all configuration
     */
    public function all(): array
    {
        return $this->config;
    }

    /**
     * Load configuration from file
     */
    public function loadFromFile(string $file): self
    {
        if (file_exists($file)) {
            $ext = pathinfo($file, PATHINFO_EXTENSION);

            if ($ext === 'php') {
                $loaded = include $file;
            } elseif ($ext === 'json') {
                $loaded = json_decode(file_get_contents($file), true);
            } else {
                return $this;
            }

            if (is_array($loaded)) {
                $this->config = array_merge($this->config, $loaded);
            }
        }

        return $this;
    }
}
