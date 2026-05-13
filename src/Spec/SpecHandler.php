<?php

namespace Diplodocus\Spec;

interface SpecHandler
{
    public function getName(): string;

    public function getPages(string $projectPath): array;

    /**
     * Where bare image filenames in this page should resolve to, relative to the project path.
     * flat-numbered → "attachments/" (the conventional asset folder).
     * feature-driven → "{feature}/" (the page's own feature folder).
     * Returns a trailing-slash-terminated relative path, or "" if assets live alongside the page.
     */
    public function getAssetBase(string $projectPath, string $pageSlug): string;

    /**
     * Sidebar tree shape — a list of entries where each is either:
     *   ['type' => 'page',  'slug' => …, 'name' => …]
     *   ['type' => 'group', 'slug' => …, 'name' => …, 'leadSlug' => …, 'children' => […pages…]]
     * Groups carry a `leadSlug` so clicking the group label lands on the first child
     * (the brief, for feature-driven). Flat-numbered returns all pages; feature-driven
     * returns one group per feature with its docs as children.
     */
    public function getSidebarTree(string $projectPath): array;
}
