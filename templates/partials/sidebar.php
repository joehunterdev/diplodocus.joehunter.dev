<?php
/**
 * Sidebar Partial Template (Mintlify-aligned)
 *
 * Variables:
 *  - $projects, $pages, $currentProject, $currentPage, $logoUrl, $appName
 */

use Diplodocus\TemplateEngine as T;
?>
<aside id="sidebar" class="nv-sidebar">
    <!-- Brand -->
    <a href="?" class="nv-sidebar-brand">
        <?php if (!empty($logoUrl)): ?>
            <img src="<?= T::e($logoUrl) ?>" alt="" style="height:1.5rem;width:auto;">
        <?php else: ?>
            <span class="nv-sidebar-brand-mark">D</span>
        <?php endif; ?>
        <span class="nv-sidebar-brand-name"><?= T::e($appName ?? 'Diplodocus') ?></span>
    </a>

    <!-- Search -->
    <div style="position:relative;">
        <svg class="nv-sidebar-search-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
        </svg>
        <input type="text" id="sidebar-search" class="nv-sidebar-search"
               placeholder="Search documentation…">
    </div>

    <!-- Navigation -->
    <nav style="flex:1; overflow-y:auto;">
        <?php foreach ($projects as $project): ?>
            <?php $isActive = $project['slug'] === ($currentProject ?? ''); ?>
            <div class="nv-sidebar-group">
                <a href="?project=<?= urlencode($project['slug']) ?>"
                   class="nv-sidebar-link<?= $isActive ? ' is-active' : '' ?>">
                    <?= T::e($project['name']) ?>
                </a>

                <?php if ($isActive && !empty($pages)): ?>
                <div class="nv-sidebar-children">
                    <?php foreach ($pages as $page): ?>
                        <?php $pageActive = $page['slug'] === ($currentPage ?? ''); ?>
                        <a href="?project=<?= urlencode($project['slug']) ?>&page=<?= urlencode($page['slug']) ?>"
                           class="nv-sidebar-child<?= $pageActive ? ' is-active' : '' ?>">
                            <?= T::e($page['name']) ?>
                        </a>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </nav>

    <!-- Footer -->
    <div class="nv-sidebar-footer">
        <?php if (!empty($currentProject)): ?>
        <a href="?action=download-project&project=<?= urlencode($currentProject) ?>" class="nv-sidebar-link">
            Download Project
        </a>
        <?php endif; ?>
        <a href="?validate=1" class="nv-sidebar-link">Validate Documentation</a>
    </div>
</aside>
