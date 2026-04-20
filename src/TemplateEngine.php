<?php
/**
 * TemplateEngine - Simple PHP template renderer
 */

namespace Diplodocus;

class TemplateEngine
{
    private string $templatePath;
    private ?string $layout = null;
    private array $globalData = [];
    
    public function __construct(string $templatePath)
    {
        $this->templatePath = rtrim($templatePath, '/\\');
    }
    
    /**
     * Set the layout template
     */
    public function setLayout(string $layout): self
    {
        $this->layout = $layout;
        return $this;
    }
    
    /**
     * Add global data available to all templates
     */
    public function addGlobal(string $key, $value): self
    {
        $this->globalData[$key] = $value;
        return $this;
    }
    
    /**
     * Render a template with data
     */
    public function render(string $template, array $data = []): string
    {
        $templateFile = $this->resolveTemplatePath($template);
        
        if (!file_exists($templateFile)) {
            throw new \RuntimeException("Template not found: {$template}");
        }
        
        // Merge global data with local data
        $data = array_merge($this->globalData, $data);
        
        // Render the template
        $content = $this->renderFile($templateFile, $data);
        
        // If layout is set, wrap content in layout
        if ($this->layout) {
            $layoutFile = $this->resolveTemplatePath($this->layout);
            if (file_exists($layoutFile)) {
                $data['content'] = $content;
                $content = $this->renderFile($layoutFile, $data);
            }
        }
        
        return $content;
    }
    
    /**
     * Render a partial template
     */
    public function partial(string $template, array $data = []): string
    {
        $templateFile = $this->resolveTemplatePath($template);
        
        if (!file_exists($templateFile)) {
            return '';
        }
        
        return $this->renderFile($templateFile, array_merge($this->globalData, $data));
    }
    
    /**
     * Resolve template path
     */
    private function resolveTemplatePath(string $template): string
    {
        // Add .php extension if not present
        if (!str_ends_with($template, '.php')) {
            $template .= '.php';
        }
        
        return $this->templatePath . DIRECTORY_SEPARATOR . $template;
    }
    
    /**
     * Render a file with data extraction
     */
    private function renderFile(string $file, array $data): string
    {
        // Extract data to local variables
        extract($data, EXTR_SKIP);
        
        // Make template engine available in templates
        $engine = $this;
        
        // Capture output
        ob_start();
        include $file;
        return ob_get_clean();
    }
    
    /**
     * Escape HTML entities
     */
    public static function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Shorthand for escape
     */
    public static function e(string $value): string
    {
        return self::escape($value);
    }
}
