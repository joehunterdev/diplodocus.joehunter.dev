<?php
/**
 * App - Main application bootstrap and request handler
 */

namespace Diplodocus;

require_once __DIR__ . '/Config.php';
require_once __DIR__ . '/Router.php';
require_once __DIR__ . '/ProjectManager.php';
require_once __DIR__ . '/ContentRenderer.php';
require_once __DIR__ . '/Validator.php';
require_once __DIR__ . '/TemplateEngine.php';

class App
{
    private Config $config;
    private Router $router;
    private ProjectManager $projectManager;
    private ContentRenderer $renderer;
    private Validator $validator;
    private TemplateEngine $template;
    
    public function __construct()
    {
        $this->config = Config::getInstance();
        $basePath = $this->config->get('base_path');
        $spacesPath = $this->config->get('spaces_path');
        $excludedDirs = $this->config->get('excluded_dirs', []);

        $this->router = new Router($spacesPath);
        $this->projectManager = new ProjectManager($spacesPath, $excludedDirs);
        $this->renderer = new ContentRenderer($spacesPath);
        $this->validator = new Validator($basePath);
        $this->template = new TemplateEngine($this->config->get('templates_path'));
    }

    /**
     * Run the application
     */
    public function run(): void
    {
        // Route the request
        $route = $this->router->route();

        // Handle attachment / file requests
        if ($route['type'] === 'file') {
            $this->router->serveFile();
            return;
        }

        // Handle documentation requests
        $project = $route['project'];
        $page = $route['page'];

        // Get projects and pages
        $projects = $this->projectManager->getProjects();
        $pages = [];
        $content = null;
        $toc = [];
        $validationResults = null;

        // Validate if requested
        if (isset($_GET['validate'])) {
            $validationResults = $this->validator->validateAll();
        }

        // Default landing: first project, first page — so the site works with
        // zero query params on a fresh install.
        if (!$project && !empty($projects)) {
            $project = $projects[0]['slug'];
        }

        // Get pages for current project
        if ($project) {
            $pages = $this->projectManager->getPages($project);

            if (!$page && !empty($pages)) {
                $page = $pages[0]['slug'];
            }

            // Render content for current page
            if ($page) {
                $rendered = $this->renderer->render($project, $page);
                if ($rendered) {
                    $content = $rendered['html'];
                    $toc     = $rendered['toc'];
                }
            }
        }
        
        // Prepare template data
        $data = [
            'config' => $this->config,
            'projects' => $projects,
            'pages' => $pages,
            'currentProject' => $project,
            'currentPage' => $page,
            'content' => $content,
            'toc' => $toc,
            'validationResults' => $validationResults,
            'hasSecurityIssues' => $validationResults ? !empty($validationResults['security']) : false,
            'hasLintIssues' => $validationResults ? !empty($validationResults['lint']) : false,
        ];
        
        // Add global template data
        $this->template->addGlobal('appName', $this->config->get('app_name'));
        $this->template->addGlobal('logoUrl', $this->config->get('logo_url'));

        // Render through the template engine — single rendering path.
        // All escaping goes through T::e() in templates/.
        $this->template->setLayout('layout');
        echo $this->template->render('content', $data);
    }

    /**
     * Get config instance
     */
    public function getConfig(): Config
    {
        return $this->config;
    }
    
    /**
     * Get validator instance
     */
    public function getValidator(): Validator
    {
        return $this->validator;
    }
}
