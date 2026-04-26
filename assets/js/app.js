/**
 * ===========================================
 * app.js — Application Bootstrap
 * ===========================================
 *
 * Single entry point. Imports all modules and initialises them
 * on DOMContentLoaded in priority order.
 *
 * Vendor scripts (jQuery shim, highlight, mermaid) are loaded
 * as plain <script> tags before this module in layout.php because
 * they are not ES modules.
 *
 * To add a new module:
 *   1. Create assets/js/modules/my-module.js
 *   2. Add `export default MyModule;` at the bottom
 *   3. Import and call .init() here
 */

import Sidebar from './modules/sidebar.js';
import Search from './modules/search.js';
import TOC from './modules/toc.js';
import CodeHighlight from './modules/code-highlight.js';
import AttachmentGallery from './modules/attachment-gallery.js';
import MermaidDiagrams from './modules/mermaid-diagrams.js';
import InteractiveContent from './modules/interactive-content.js';
import PageComments from './modules/page-comments.js';
import Annotation from './modules/annotation.js';

document.addEventListener('DOMContentLoaded', function () {
    // Priority order: layout → navigation → content → features
    Sidebar.init();
    Search.init();
    TOC.init();
    CodeHighlight.init();
    AttachmentGallery.init();
    MermaidDiagrams.init();
    InteractiveContent.init();
    PageComments.init();
    Annotation.init();

    console.log('[App] Initialized');
});
