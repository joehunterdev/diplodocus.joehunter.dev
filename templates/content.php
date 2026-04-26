<?php

/**
 * Content Template
 *
 * Three views:
 *   1. Home dashboard  — no project selected
 *   2. Space landing   — project selected, no page
 *   3. Page view       — project + page selected
 */

use Diplodocus\TemplateEngine as T;
?>

<?php if ($content): ?>
    <!-- ── PAGE VIEW ── -->
    <article data-content-article class="prose">
        <?= $content ?>
    </article>

<?php elseif (!empty($currentProject)): ?>
    <!-- ── SPACE LANDING ── -->
    <?php
    $spaceName = '';
    foreach ($projects as $p) {
        if ($p['slug'] === $currentProject) {
            $spaceName = $p['name'];
            break;
        }
    }
    ?>
    <div class="dc-dashboard">
        <div class="dc-dashboard-header">
            <h1 class="dc-dashboard-title" id=""><?= T::e($spaceName) ?></h1>
            <p class="dc-dashboard-sub"><?= count($pages) ?> page<?= count($pages) !== 1 ? 's' : '' ?></p>
        </div>

        <?php if (!empty($pages)): ?>
            <div class="dc-card-grid">
                <?php foreach ($pages as $i => $page): ?>
                    <a href="<?= $router->url(['project' => $currentProject, 'page' => $page['slug']]) ?>"
                        class="dc-card">
                        <div class="dc-card-num"><?= str_pad($i + 1, 2, '0', STR_PAD_LEFT) ?></div>
                        <div class="dc-card-title"><?= T::e($page['name']) ?></div>
                        <svg class="dc-card-arrow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

<?php else: ?>
    <!-- ── HOME DASHBOARD ── -->
    <div class="dc-dashboard">
        <div class="dc-dashboard-hero">
            <div class="dc-dashboard-hero-text">
                <h1 class="dc-dashboard-title">Welcome to <?= T::e($appName ?? 'Diplodocus') ?></h1>
                <p class="dc-dashboard-sub">Select a project below to get started.</p>
            </div>
            <?php $logoUrl = $config ? $config->get('logo_url') : ''; ?>
            <?php if ($logoUrl): ?>
                <div class="dc-dashboard-hero-image">
                    <img src="<?= T::e($logoUrl) ?>" alt="<?= T::e($appName ?? 'Diplodocus') ?>" />
                </div>
            <?php endif; ?>
        </div>

        <?php if (!empty($projects)): ?>
            <div class="dc-card-grid">
                <?php foreach ($projects as $project): ?>
                    <a href="<?= $router->url(['project' => $project['slug']]) ?>" class="dc-card">
                        <div class="dc-card-icon">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 7a2 2 0 012-2h4l2 2h8a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V7z" />
                            </svg>
                        </div>
                        <div class="dc-card-title"><?= T::e($project['name']) ?></div>
                        <div class="dc-card-desc"><?= (int)$project['fileCount'] ?> page<?= $project['fileCount'] !== 1 ? 's' : '' ?></div>
                        <svg class="dc-card-arrow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p style="color:var(--dc-text-muted);">No spaces found. Add folders with <code>.md</code> files inside <code>public/</code>.</p>
        <?php endif; ?>
    </div>
<?php endif; ?>