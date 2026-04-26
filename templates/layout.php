<?php

/**
 * Main Layout Template
 * 
 * Variables available:
 * - $content: The main content to display
 * - $appName: Application name
 * - $logoUrl: Logo URL
 * - $config: Config instance
 */

use Diplodocus\TemplateEngine as T;
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <?php
    $seo = $seo ?? [];
    $metaTitle       = $seo['title']       ?? ($title ?? $appName);
    $metaDescription = $seo['description'] ?? 'Diplodocus — a markdown-first documentation site by Joe Hunter.';
    $metaCanonical   = $seo['canonical']   ?? '';
    $metaRobots      = $seo['robots']      ?? 'index,follow';
    ?>

    <title><?= T::e($metaTitle) ?></title>
    <meta name="description" content="<?= T::e($metaDescription) ?>">
    <meta name="author" content="Joe Hunter">
    <meta name="robots" content="<?= T::e($metaRobots) ?>">
    <?php if ($metaCanonical): ?>
        <link rel="canonical" href="<?= T::e($metaCanonical) ?>">
    <?php endif; ?>

    <!-- Open Graph -->
    <meta property="og:type" content="website">
    <meta property="og:title" content="<?= T::e($metaTitle) ?>">
    <meta property="og:description" content="<?= T::e($metaDescription) ?>">
    <meta property="og:image" content="https://diplodocus.joehunter.dev/example.png">
    <?php if ($metaCanonical): ?>
        <meta property="og:url" content="<?= T::e($metaCanonical) ?>">
    <?php endif; ?>

    <!-- Twitter card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= T::e($metaTitle) ?>">
    <meta name="twitter:description" content="<?= T::e($metaDescription) ?>">
    <meta name="twitter:image" content="https://diplodocus.joehunter.dev/example.png">

    <link rel="author" href="https://joehunter.es">
    <link rel="icon" href="/favicon.ico">

    <?php $v = $config ? ($config->get('version') ?: '1') : '1'; ?>

    <!-- Styles -->
    <link rel="stylesheet" href="/assets/css/theme.css?v=<?= $v ?>">
    <link rel="stylesheet" href="/assets/css/tailwind.min.css?v=<?= $v ?>">
    <link rel="stylesheet" href="/assets/css/highlight-dark.min.css?v=<?= $v ?>">
    <link rel="stylesheet" href="/assets/css/diplodocus.css?v=<?= $v ?>">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>

<body>
    <div style="display:flex; min-height:100vh;">
        <?= $engine->partial('partials/sidebar', get_defined_vars()) ?>

        <div style="flex:1; display:flex; flex-direction:column; min-width:0;">
            <?= $engine->partial('partials/header', get_defined_vars()) ?>

            <main data-main-scroll style="flex:1; display:flex;">
                <div class="dc-main">
                    <div class="dc-main-inner">
                        <?= $content ?>
                    </div>
                </div>

                <?php if (!empty($toc)): ?>
                    <?= $engine->partial('partials/toc', get_defined_vars()) ?>
                <?php endif; ?>
            </main>
        </div>
    </div>

    <?= $engine->partial('partials/modals', get_defined_vars()) ?>

    <!-- Scripts -->
    <script src="/assets/js/vendor/highlight.min.js?v=<?= $v ?>"></script>
    <script src="/assets/js/utils/jquery-compat.js?v=<?= $v ?>"></script>
    <script src="/assets/js/utils/page-logic.js?v=<?= $v ?>"></script>
    <script src="/assets/js/vendor/mermaid.min.js?v=<?= $v ?>"></script>
    <script src="/assets/js/modules/sidebar.js?v=<?= $v ?>"></script>
    <script src="/assets/js/modules/search.js?v=<?= $v ?>"></script>
    <script src="/assets/js/modules/toc.js?v=<?= $v ?>"></script>
    <script src="/assets/js/modules/code-highlight.js?v=<?= $v ?>"></script>
    <script src="/assets/js/modules/attachment-gallery.js?v=<?= $v ?>"></script>
    <script src="/assets/js/modules/mermaid-diagrams.js?v=<?= $v ?>"></script>
    <script src="/assets/js/modules-init.js?v=<?= $v ?>"></script>
    <script src="/assets/js/app.js?v=<?= $v ?>"></script>
</body>

</html>