/**
 * PageLogic — Module Registry
 * Registers and initialises modules in priority order.
 *
 * Usage:
 *   PageLogic.register('sidebar', Sidebar.init, { context: 'global', priority: 90 });
 *   PageLogic.initAll();
 */

const PageLogic = (function () {
    'use strict';

    const _modules = [];

    /**
     * Register a module for initialisation.
     * @param {string}   name     - Unique module name
     * @param {Function} initFn   - The module's init() function
     * @param {object}   options
     * @param {string}   options.context  - 'global' | 'auth' | 'admin' (default: 'global')
     * @param {number}   options.priority - Higher runs first: 100=critical, 90=layout, 80=nav, 70=features
     */
    function register(name, initFn, options) {
        _modules.push({
            name: name,
            initFn: initFn,
            options: Object.assign({ context: 'global', priority: 50 }, options || {}),
        });
    }

    /**
     * Initialise all registered modules, sorted by priority descending.
     */
    function initAll() {
        var sorted = _modules.slice().sort(function (a, b) {
            return b.options.priority - a.options.priority;
        });

        sorted.forEach(function (m) {
            try {
                m.initFn();
            } catch (e) {
                console.error('[PageLogic] Failed to init module "' + m.name + '":', e);
            }
        });
    }

    return { register: register, initAll: initAll };

})();

window.PageLogic = PageLogic;
