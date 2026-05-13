<?php

namespace Diplodocus\Spec;

class FlatNumberedSpec implements SpecHandler
{
    public function getName(): string
    {
        return 'flat-numbered';
    }

    public function getPages(string $projectPath): array
    {
        if (!is_dir($projectPath)) {
            return [];
        }

        $files = glob($projectPath . DIRECTORY_SEPARATOR . '*.md');
        $pages = [];

        foreach ($files as $file) {
            $filename = basename($file, '.md');
            if (preg_match('/^(\d+)-(.+)$/', $filename, $matches)) {
                $pages[] = [
                    'order' => (int)$matches[1],
                    'slug'  => $filename,
                    'name'  => $this->formatName($matches[2]),
                    'path'  => $file,
                ];
            }
        }

        usort($pages, fn($a, $b) => $a['order'] <=> $b['order']);

        return $pages;
    }

    public function getAssetBase(string $projectPath, string $pageSlug): string
    {
        return 'attachments/';
    }

    public function getSidebarTree(string $projectPath): array
    {
        $tree = [];
        foreach ($this->getPages($projectPath) as $page) {
            $tree[] = [
                'type' => 'page',
                'slug' => $page['slug'],
                'name' => $page['name'],
            ];
        }
        return $tree;
    }

    private function formatName(string $name): string
    {
        $name = str_replace(['.', '-', '_'], ' ', $name);
        return ucwords($name);
    }
}
