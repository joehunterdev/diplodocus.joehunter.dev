/**
 * ===========================================
 * Sidebar
 * ===========================================
 *
 * Toggle sidebar visibility with localStorage persistence.
 * Closes automatically on mobile when clicking outside.
 *
 * @usage HTML
 *   <aside data-sidebar>...</aside>
 *   <button data-sidebar-toggle>...</button>
 */

const Sidebar = (function () {
    'use strict';

    // -- Private: Config --
    const CONFIG = {
        debug: false,
        eventNamespace: '.sidebar',
        breakpoint: 1024,
        storageKey: 'sidebar-hidden',
    };

    // -- Private: Selectors --
    const SELECTORS = {
        sidebar: '[data-sidebar]',
        toggle: '[data-sidebar-toggle]',
    };

    // -- Private: State --
    let state = {
        initialized: false,
        hidden: false,
    };

    // -- Private: Cached DOM --
    let $sidebar = null;
    let $toggle = null;

    // -- Private: Utils --
    function log(...args) {
        if (CONFIG.debug) console.log('[Sidebar]', ...args);
    }

    function setState(newState) {
        state = Object.assign({}, state, newState);
    }

    // -- Private: Setup --
    function cacheDom() {
        const $ = window.jQuery;
        $sidebar = $('[data-sidebar]');
        $toggle = $('[data-sidebar-toggle]');
    }

    function setup() {
        cacheDom();
        return $sidebar.length > 0;
    }

    // -- Private: Core --
    function isMobile() {
        return window.innerWidth < 769;
    }

    function toggle() {
        const $ = window.jQuery;
        if (isMobile()) {
            // On mobile: sidebar is hidden by CSS; open via .is-open
            $sidebar.toggleClass('is-open');
            $('body').toggleClass('is-sidebar-open', $sidebar.hasClass('is-open'));
            setState({ hidden: !$sidebar.hasClass('is-open') });
        } else {
            $sidebar.toggleClass('hidden');
            setState({ hidden: $sidebar.hasClass('hidden') });
            saveState();
        }
        log('Toggled, hidden:', state.hidden);
    }

    function close() {
        const $ = window.jQuery;
        if (isMobile()) {
            $sidebar.removeClass('is-open');
            $('body').removeClass('is-sidebar-open');
            setState({ hidden: true });
        } else {
            $sidebar.addClass('hidden');
            setState({ hidden: true });
            saveState();
        }
    }

    function saveState() {
        try { localStorage.setItem(CONFIG.storageKey, state.hidden); } catch (e) {}
    }

    function restoreState() {
        if (window.innerWidth >= CONFIG.breakpoint) {
            try {
                var isHidden = localStorage.getItem(CONFIG.storageKey) === 'true';
                if (isHidden) {
                    $sidebar.addClass('hidden');
                    setState({ hidden: true });
                }
            } catch (e) {}
        }
    }

    // -- Private: Events --
    function bindEvents() {
        const $ = window.jQuery;

        $toggle.on('click' + CONFIG.eventNamespace, function (e) {
            e.preventDefault();
            toggle();
        });

        $(document).on('click' + CONFIG.eventNamespace, function (e) {
            if (window.innerWidth < CONFIG.breakpoint) {
                if (!$(e.target).closest(SELECTORS.sidebar + ', ' + SELECTORS.toggle).length) {
                    close();
                }
            }
        });
    }

    function unbindEvents() {
        const $ = window.jQuery;
        if ($toggle) $toggle.off(CONFIG.eventNamespace);
        $(document).off(CONFIG.eventNamespace);
    }

    // -- Public: Init --
    function init() {
        const $ = window.jQuery;
        if (!$) { console.warn('[Sidebar] jQuery not available'); return; }
        if (!setup()) return;
        if (state.initialized) return;

        bindEvents();
        restoreState();

        setState({ initialized: true });
        log('Initialized');
    }

    // -- Public: Destroy --
    function destroy() {
        if (!state.initialized) return;
        unbindEvents();
        setState({ initialized: false });
        log('Destroyed');
    }

    // -- Public API --
    return { init: init, destroy: destroy };

})();

window.Sidebar = Sidebar;
