<?php
/**
 * Content Template (Mintlify-aligned)
 */

use Diplodocus\TemplateEngine as T;
?>
<?php if ($content): ?>
    <article class="prose">
        <?= $content ?>
    </article>
<?php else: ?>
    <!-- Welcome / Landing -->
    <div>
        <div style="text-align:center; margin-bottom:2.5rem;">
            <h1 style="font-size:2.5rem; font-weight:700; color:var(--nv-brand-primary); margin-bottom:0.75rem; letter-spacing:-0.02em;">
                Welcome to <?= T::e($appName ?? 'Documentation') ?>
            </h1>
            <p style="font-size:1.125rem; color:var(--nv-text-muted);">
                Select a project from the sidebar to get started.
            </p>
        </div>

        <?php if (!empty($projects)): ?>
        <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(260px, 1fr)); gap:1rem;">
            <?php foreach ($projects as $project): ?>
            <a href="?project=<?= urlencode($project['slug']) ?>" class="nv-card">
                <div class="nv-card-icon">
                    <svg style="width:1rem;height:1rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <div class="nv-card-title"><?= T::e($project['name']) ?></div>
                <div class="nv-card-desc">
                    View documentation for <?= T::e($project['name']) ?>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <div style="margin-top:3rem; padding-top:2rem; border-top:1px solid var(--nv-border-subtle); text-align:center;">
            <a href="?validate=1" class="nv-header-toggle" style="color:var(--nv-text-secondary);">
                <svg style="width:1rem;height:1rem;margin-right:0.5rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Validate All Documentation
            </a>
        </div>
    </div>
<?php endif; ?>
