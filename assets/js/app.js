/**
 * ===========================================
 * app.js — Application Bootstrap
 * ===========================================
 *
 * Waits for DOM ready, then fires PageLogic.initAll()
 * which initialises all registered modules in priority order.
 *
 * Module registration lives in modules-init.js.
 * Individual modules live in modules/*.js.
 * Utilities live in utils/*.js.
 */

(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        if (typeof PageLogic === 'undefined') {
            console.error('[App] PageLogic not found.');
            return;
        }

        PageLogic.initAll();
        console.log('[App] Initialized');
    });

})();
