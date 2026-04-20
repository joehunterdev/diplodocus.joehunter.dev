<?php

/**
 * Header Partial Template (Mintlify-aligned)
 */

use Diplodocus\TemplateEngine as T;
?>
<header class="nv-header">
    <nav class="nv-breadcrumb">
        <a href="/">Home</a>

        <?php if (!empty($currentProject)): ?>
            <svg class="nv-breadcrumb-sep" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
            <a href="<?= $router->url(['project' => $currentProject]) ?>">
                <?= T::e(ucwords(str_replace(['-', '_'], ' ', $currentProject))) ?>
            </a>
        <?php endif; ?>

        <?php if (!empty($currentPage)): ?>
            <svg class="nv-breadcrumb-sep" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
            <span class="nv-breadcrumb-current">
                <?= T::e(ucwords(str_replace(['-', '_'], ' ', preg_replace('/^\d+-/', '', $currentPage)))) ?>
            </span>
        <?php endif; ?>
    </nav>

    <button id="sidebar-toggle" class="nv-header-toggle" title="Toggle Sidebar">
        <svg style="width:1rem;height:1rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
        </svg>
    </button>
</header>