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
    <div class="nv-dashboard">
        <div class="nv-dashboard-header">
            <h1 class="nv-dashboard-title"><?= T::e($spaceName) ?></h1>
            <p class="nv-dashboard-sub"><?= count($pages) ?> page<?= count($pages) !== 1 ? 's' : '' ?></p>
        </div>

        <?php if (!empty($pages)): ?>
            <div class="nv-card-grid">
                <?php foreach ($pages as $i => $page): ?>
                    <a href="<?= $router->url(['project' => $currentProject, 'page' => $page['slug']]) ?>"
                        class="nv-card">
                        <div class="nv-card-num"><?= str_pad($i + 1, 2, '0', STR_PAD_LEFT) ?></div>
                        <div class="nv-card-title"><?= T::e($page['name']) ?></div>
                        <svg class="nv-card-arrow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

<?php else: ?>
    <!-- ── HOME DASHBOARD ── -->
    <div class="nv-dashboard">
        <div class="nv-dashboard-header">
            <h1 class="nv-dashboard-title">Welcome to <?= T::e($appName ?? 'Diplodocus') ?></h1>
            <p class="nv-dashboard-sub">Select a space below to get started.</p>
        </div>

        <?php if (!empty($projects)): ?>
            <div class="nv-card-grid">
                <?php foreach ($projects as $project): ?>
                    <a href="<?= $router->url(['project' => $project['slug']]) ?>" class="nv-card">
                        <div class="nv-card-icon">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 7a2 2 0 012-2h4l2 2h8a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V7z" />
                            </svg>
                        </div>
                        <div class="nv-card-title"><?= T::e($project['name']) ?></div>
                        <div class="nv-card-desc"><?= (int)$project['fileCount'] ?> page<?= $project['fileCount'] !== 1 ? 's' : '' ?></div>
                        <svg class="nv-card-arrow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p style="color:var(--nv-text-muted);">No spaces found. Add folders with <code>.md</code> files inside <code>public/</code>.</p>
        <?php endif; ?>
    </div>
<?php endif; ?>