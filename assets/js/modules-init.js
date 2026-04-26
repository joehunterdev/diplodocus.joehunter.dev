/**
 * ===========================================
 * modules-init.js
 * ===========================================
 *
 * Single registration point for all modules.
 * Loaded after all module files, before app.js.
 */

(function () {
    'use strict';

    if (typeof PageLogic === 'undefined') {
        console.error('[modules-init] PageLogic not found. Ensure utils/page-logic.js is loaded first.');
        return;
    }

    PageLogic.register('sidebar', Sidebar.init, { context: 'global', priority: 90 });
    PageLogic.register('search', Search.init, { context: 'global', priority: 80 });
    PageLogic.register('toc', TOC.init, { context: 'global', priority: 80 });
    PageLogic.register('codeHighlight', CodeHighlight.init, { context: 'global', priority: 70 });
    PageLogic.register('attachmentGallery', AttachmentGallery.init, { context: 'global', priority: 70 });
    PageLogic.register('mermaidDiagrams', MermaidDiagrams.init, { context: 'global', priority: 60 });

})();
