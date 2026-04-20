/**
 * ===========================================
 * Search
 * ===========================================
 *
 * Debounced sidebar search that highlights matching nav links.
 * Clears on Escape.
 *
 * @usage HTML
 *   <input type="text" data-sidebar-search placeholder="Search...">
 *   <a data-nav-link href="...">Page title</a>
 */

const Search = (function () {
    'use strict';

    // -- Private: Config --
    const CONFIG = {
        debug: false,
        eventNamespace: '.search',
        debounceMs: 300,
        minLength: 2,
        matchClass: 'is-search-match',
    };

    // -- Private: Selectors --
    const SELECTORS = {
        input: '[data-sidebar-search]',
        navLinks: '[data-nav-link]',
    };

    // -- Private: State --
    let state = {
        initialized: false,
        query: '',
    };

    // -- Private: Cached DOM --
    let $input = null;
    let debounceTimer = null;

    // -- Private: Utils --
    function log(...args) {
        if (CONFIG.debug) console.log('[Search]', ...args);
    }

    function setState(newState) {
        state = Object.assign({}, state, newState);
    }

    // -- Private: Setup --
    function cacheDom() {
        $input = window.jQuery(SELECTORS.input);
    }

    function setup() {
        cacheDom();
        return $input.length > 0;
    }

    // -- Private: Core --
    function search(query) {
        const $ = window.jQuery;
        setState({ query: query });

        if (!query || query.length < CONFIG.minLength) {
            clearHighlights();
            return;
        }

        var q = query.toLowerCase();

        $(SELECTORS.navLinks).each(function () {
            var $link = $(this);
            var text = $link.text().toLowerCase();
            if (text.indexOf(q) !== -1) {
                $link.addClass(CONFIG.matchClass);
            } else {
                $link.removeClass(CONFIG.matchClass);
            }
        });

        log('Searched:', query);
    }

    function clearHighlights() {
        window.jQuery(SELECTORS.navLinks).removeClass(CONFIG.matchClass);
    }

    // -- Private: Events --
    function bindEvents() {
        $input.on('input' + CONFIG.eventNamespace, function () {
            clearTimeout(debounceTimer);
            var val = window.jQuery(this).val();
            debounceTimer = setTimeout(function () {
                search(val);
            }, CONFIG.debounceMs);
        });

        $input.on('keydown' + CONFIG.eventNamespace, function (e) {
            if (e.key === 'Escape') {
                window.jQuery(this).val('');
                clearHighlights();
                setState({ query: '' });
            }
        });
    }

    function unbindEvents() {
        if ($input) $input.off(CONFIG.eventNamespace);
    }

    // -- Public: Init --
    function init() {
        const $ = window.jQuery;
        if (!$) { console.warn('[Search] jQuery not available'); return; }
        if (!setup()) return;
        if (state.initialized) return;

        bindEvents();

        setState({ initialized: true });
        log('Initialized');
    }

    // -- Public: Destroy --
    function destroy() {
        if (!state.initialized) return;
        clearTimeout(debounceTimer);
        unbindEvents();
        setState({ initialized: false });
        log('Destroyed');
    }

    // -- Public API --
    return { init: init, destroy: destroy };

})();

window.Search = Search;
