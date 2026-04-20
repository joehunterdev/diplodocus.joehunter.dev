<?php
/**
 * Table of Contents Partial (Mintlify-aligned)
 */

use Diplodocus\TemplateEngine as T;
?>
<aside class="nv-toc">
    <h4 class="nv-toc-heading">On this page</h4>
    <nav class="nv-toc-list">
        <?php foreach ($toc as $item): ?>
            <a href="#<?= T::e($item['id']) ?>"
               class="nv-toc-link toc-link"
               data-level="<?= (int)$item['level'] ?>"
               data-target="<?= T::e($item['id']) ?>">
                <?= T::e($item['text']) ?>
            </a>
        <?php endforeach; ?>
    </nav>
</aside>
