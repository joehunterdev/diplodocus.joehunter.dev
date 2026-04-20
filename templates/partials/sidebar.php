<?php

/**
 * Sidebar Partial Template
 *
 * Home  (no $currentProject): lists all spaces
 * Space (with $currentProject): back link + that space's pages only
 */

use Diplodocus\TemplateEngine as T;
?>
<aside data-sidebar class="nv-sidebar">

    <!-- Brand -->
    <a href="/" class="nv-sidebar-brand">
        <?php if (!empty($logoUrl)): ?>
            <?php $logo = ($logoUrl[0] === '/' || strpos($logoUrl, 'http') === 0) ? $logoUrl : '/' . $logoUrl; ?>
            <img src="<?= T::e($logo) ?>" alt="" style="height:1.5rem;width:auto;">
        <?php else: ?>
            <span class="nv-sidebar-brand-mark">D</span>
        <?php endif; ?>
        <span class="nv-sidebar-brand-name"><?= T::e($appName ?? 'Diplodocus') ?></span>
    </a>

    <!-- Search -->
    <div style="position:relative;">
        <svg class="nv-sidebar-search-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
        </svg>
        <input type="text" data-sidebar-search class="nv-sidebar-search"
            placeholder="Search documentation…">
    </div>

    <nav style="flex:1; overflow-y:auto; padding-top:.5rem;">

        <?php if (empty($currentProject)): ?>
            <!-- ── HOME: list all spaces ── -->
            <p class="nv-sidebar-section-label">Spaces</p>
            <?php foreach ($projects as $project): ?>
                <a href="<?= $router->url(['project' => $project['slug']]) ?>"
                    data-nav-link class="nv-sidebar-link">
                    <?= T::e($project['name']) ?>
                    <span class="nv-sidebar-badge"><?= (int)$project['fileCount'] ?></span>
                </a>
            <?php endforeach; ?>

        <?php else: ?>
            <!-- ── SPACE: back link + pages ── -->
            <a href="?" data-nav-link class="nv-sidebar-back">
                <svg style="width:.8rem;height:.8rem;margin-right:.4rem;flex-shrink:0;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                All Spaces
            </a>

            <?php
            $spaceName = '';
            foreach ($projects as $p) {
                if ($p['slug'] === $currentProject) {
                    $spaceName = $p['name'];
                    break;
                }
            }
            ?>
            <p class="nv-sidebar-section-label" style="margin-top:.75rem;">
                <?= T::e($spaceName) ?>
            </p>

            <?php foreach ($pages as $page): ?>
                <?php $isActive = $page['slug'] === ($currentPage ?? ''); ?>
                <a href="<?= $router->url(['project' => $currentProject, 'page' => $page['slug']]) ?>"
                    data-nav-link class="nv-sidebar-child<?= $isActive ? ' is-active' : '' ?>">
                    <?= T::e($page['name']) ?>
                </a>
            <?php endforeach; ?>
        <?php endif; ?>

    </nav>

    <!-- Footer -->
    <div class="nv-sidebar-footer">
        <?php if (!empty($currentProject)): ?>
            <a href="<?= $router->url(['action' => 'download-project', 'project' => $currentProject]) ?>" class="nv-sidebar-link">
                <svg style="width:.85rem;height:.85rem;margin-right:.4rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                Download Space
            </a>
        <?php endif; ?>
        <a href="/?validate=1" class="nv-sidebar-link">Validate Documentation</a>
    </div>

</aside>