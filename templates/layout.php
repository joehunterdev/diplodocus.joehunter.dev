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
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= T::e($title ?? $appName) ?></title>

    <!-- Styles (theme.css MUST be first — its vars feed every sheet after) -->
    <?php
    $styles = $config->get('stylesheets', [
        'assets/css/theme.css',
        'assets/css/tailwind.min.css',
        'assets/css/highlight-dark.min.css',
        'assets/css/diplodocus.css',
    ]);
    foreach ($styles as $sheet):
        $sheet = (strpos($sheet, 'http') === 0 || $sheet[0] === '/') ? $sheet : '/' . $sheet;
    ?>
        <link rel="stylesheet" href="<?= T::e($sheet) ?>">
    <?php endforeach; ?>

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
                <div class="nv-main">
                    <div class="nv-main-inner">
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
    <?php
    $scripts = $config->get('scripts', ['assets/js/highlight.min.js', 'assets/js/app.js']);
    foreach ($scripts as $s):
        $s = (strpos($s, 'http') === 0 || $s[0] === '/') ? $s : '/' . $s;
    ?>
        <script src="<?= T::e($s) ?>"></script>
    <?php endforeach; ?>
</body>

</html>