<?php

/**
 * Table of Contents Partial (Mintlify-aligned)
 */

use Diplodocus\TemplateEngine as T;
?>
<aside class="dc-toc">
    <h4 class="dc-toc-heading">On this page</h4>
    <nav class="dc-toc-list">
        <?php foreach ($toc as $item): ?>
            <a href="#<?= T::e($item['id']) ?>"
                data-toc-link
                class="dc-toc-link"
                data-level="<?= (int)$item['level'] ?>"
                data-target="<?= T::e($item['id']) ?>">
                <?= T::e($item['text']) ?>
            </a>
        <?php endforeach; ?>
    </nav>
</aside>