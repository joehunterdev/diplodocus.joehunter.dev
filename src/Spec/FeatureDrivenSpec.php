<?php

namespace Diplodocus\Spec;

class FeatureDrivenSpec implements SpecHandler
{
    private const DOC_TYPES = ['brief', 'plan', 'implementation'];

    public function getName(): string
    {
        return 'feature-driven';
    }

    public function getPages(string $projectPath): array
    {
        if (!is_dir($projectPath)) {
            return [];
        }

        $features = [];
        foreach (scandir($projectPath) as $entry) {
            if ($entry === '.' || $entry === '..') continue;
            $featurePath = $projectPath . DIRECTORY_SEPARATOR . $entry;
            if (!is_dir($featurePath)) continue;
            // A folder qualifies as a feature if it contains at least one of the doc files.
            if ($this->hasAnyDoc($featurePath, $entry)) {
                $features[] = $entry;
            }
        }
        sort($features, SORT_STRING);

        $pages = [];
        $order = 0;
        foreach ($features as $feature) {
            foreach (self::DOC_TYPES as $docType) {
                $filename = $feature . '-' . $docType . '.md';
                $filePath = $projectPath . DIRECTORY_SEPARATOR . $feature . DIRECTORY_SEPARATOR . $filename;
                if (!is_file($filePath)) continue;

                $slug = $feature . '-' . $docType;
                $pages[] = [
                    'order'   => ++$order,
                    'slug'    => $slug,
                    'name'    => $this->formatName($slug),
                    'path'    => $filePath,
                    'feature' => $feature,
                    'docType' => $docType,
                ];
            }
        }
        return $pages;
    }

    public function getAssetBase(string $projectPath, string $pageSlug): string
    {
        $feature = $this->featureFromSlug($pageSlug);
        return $feature !== null ? $feature . '/' : '';
    }

    public function getSidebarTree(string $projectPath): array
    {
        $byFeature = [];
        foreach ($this->getPages($projectPath) as $page) {
            $feature = $page['feature'];
            if (!isset($byFeature[$feature])) {
                $byFeature[$feature] = [];
            }
            $byFeature[$feature][] = [
                'type' => 'page',
                'slug' => $page['slug'],
                'name' => ucwords(str_replace(['.', '-', '_'], ' ', $page['docType'])),
            ];
        }

        $tree = [];
        foreach ($byFeature as $feature => $children) {
            $tree[] = [
                'type'     => 'group',
                'slug'     => $feature,
                'name'     => ucwords(str_replace(['.', '-', '_'], ' ', $feature)),
                'leadSlug' => $children[0]['slug'],
                'children' => $children,
            ];
        }
        return $tree;
    }

    private function hasAnyDoc(string $featurePath, string $featureName): bool
    {
        foreach (self::DOC_TYPES as $docType) {
            if (is_file($featurePath . DIRECTORY_SEPARATOR . $featureName . '-' . $docType . '.md')) {
                return true;
            }
        }
        return false;
    }

    private function featureFromSlug(string $slug): ?string
    {
        foreach (self::DOC_TYPES as $docType) {
            $suffix = '-' . $docType;
            if (str_ends_with($slug, $suffix)) {
                return substr($slug, 0, -strlen($suffix));
            }
        }
        return null;
    }

    private function formatName(string $name): string
    {
        $name = str_replace(['.', '-', '_'], ' ', $name);
        return ucwords($name);
    }
}
