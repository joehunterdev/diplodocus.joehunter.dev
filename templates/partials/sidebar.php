<?php

/**
 * Sidebar Partial Template
 *
 * Home  (no $currentProject): lists all spaces
 * Space (with $currentProject): back link + that space's pages only
 */

use Diplodocus\TemplateEngine as T;
?>
<aside data-sidebar class="dc-sidebar">

    <!-- Brand -->
    <a href="/" class="dc-sidebar-brand">
        <?php if (!empty($logoUrl)): ?>
            <img src="<?= T::e($logoUrl) ?>" alt="<?= T::e($appName ?? 'Diplodocus') ?>" style="height:1.75rem;width:auto;display:block;">
        <?php else: ?>
            <span class="dc-sidebar-brand-mark">D</span>
        <?php endif; ?>
        <span class="dc-sidebar-brand-name"><?= T::e($appName ?? 'Diplodocus') ?></span>
    </a>

    <!-- Search -->
    <div style="position:relative;">
        <input type="text" data-sidebar-search class="dc-sidebar-search"
            placeholder="Search documentation…">
        <div data-search-results class="dc-search-results" hidden></div>
    </div>

    <nav style="flex:1; overflow-y:auto; padding-top:.5rem;">

        <?php if (empty($currentProject)): ?>
            <!-- ── HOME: list all spaces ── -->
            <p class="dc-sidebar-section-label">Project</p>
            <?php foreach ($projects as $project): ?>
                <a href="<?= $router->url(['project' => $project['slug']]) ?>"
                    data-nav-link class="dc-sidebar-link">
                    <?= T::e($project['name']) ?>
                    <span class="dc-sidebar-badge"><?= (int)$project['fileCount'] ?></span>
                </a>
            <?php endforeach; ?>

        <?php else: ?>
            <!-- ── SPACE: back link + pages + progress ── -->
            <a href="<?= $router->url([]) ?>" data-nav-link class="dc-sidebar-back">
                <svg style="width:.8rem;height:.8rem;margin-right:.4rem;flex-shrink:0;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                All Project
            </a>

            <?php
            // Get project name from $project (if available) or fall back to $projects lookup
            $spaceName = $project['slug'] ?? $currentProject;
            if (!isset($project) || !is_array($project)) {
                foreach ($projects ?? [] as $p) {
                    if ($p['slug'] === $currentProject) {
                        $spaceName = $p['name'] ?? $p['slug'];
                        break;
                    }
                }
            }
            ?>
            <p class="dc-sidebar-section-label" style="margin-top:.75rem;">
                <?= T::e($spaceName) ?>
            </p>

            <!-- Page progress indicator (if we have project context) -->
            <?php if (isset($pageIndex) && isset($pageCount)): ?>
                <div class="dc-sidebar-progress" style="font-size:.8rem; color:var(--dc-text-muted); padding:0.5rem 1rem; margin-bottom:0.5rem;">
                    Page <?= $pageIndex + 1 ?> of <?= $pageCount ?>
                </div>
            <?php endif; ?>

            <!-- List all pages in project -->
            <?php
            $pageList = $project['pages'] ?? $pages ?? [];
            foreach ($pageList as $idx => $page):
                $isActive = false;
                if (isset($pageIndex)) {
                    $isActive = ($idx === $pageIndex);
                } else {
                    $isActive = ($page['slug'] ?? $page['name'] ?? '') === ($currentPage ?? '');
                }
                $pageHref = $page['slug'] ?? $page['name'] ?? '';
            ?>
                <a href="<?= $router->url(['project' => $currentProject, 'page' => $pageHref]) ?>"
                    data-nav-link class="dc-sidebar-child<?= $isActive ? ' is-active' : '' ?>">
                    <?= T::e($page['displayName'] ?? $page['name'] ?? ucfirst(str_replace(['-', '_'], ' ', $pageHref))) ?>
                </a>
            <?php endforeach; ?>
        <?php endif; ?>

    </nav>

    <!-- Footer -->
    <div class="dc-sidebar-footer">
        <?php if (!empty($currentProject)): ?>
            <a href="<?= $router->url(['action' => 'download-project', 'project' => $currentProject]) ?>" class="dc-sidebar-link">
                <svg style="width:.85rem;height:.85rem;margin-right:.4rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                Download Docs
            </a>
        <?php endif; ?>
    </div>

</aside>