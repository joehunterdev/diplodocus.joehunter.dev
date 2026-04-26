/**
 * ===========================================
 * InteractiveContent
 * ===========================================
 *
 * Enables task list checkboxes and persists state to localStorage.
 *
 * Parsedown renders task lists as:
 *   <li class="task-list-item"><input type="checkbox" disabled> Text</li>
 *
 * This module:
 * 1. Finds all task list checkboxes
 * 2. Removes disabled attribute
 * 3. Loads saved state from localStorage
 * 4. Saves state on change
 *
 * @usage HTML
 *   <div data-content-article>
 *       <li class="task-list-item">
 *           <input type="checkbox" disabled> Task text
 *       </li>
 *   </div>
 */

const InteractiveContent = (function () {
    'use strict';

    // -- Private: Config --
    const CONFIG = {
        debug: false,
        eventNamespace: '.interactiveContent',
        storageKey: 'dc-page-state',
    };

    // -- Private: Selectors --
    const SELECTORS = {
        contentArea: '[data-content-article]',
        checkboxes: '[data-content-article] .task-list-item input[type="checkbox"]',
    };

    // -- Private: State --
    let state = {
        initialized: false,
        checkboxCount: 0,
    };

    // -- Private: Cached DOM --
    let $contentArea = null;

    // -- Private: Utils --
    function log(...args) {
        if (CONFIG.debug) {
            console.log('[InteractiveContent]', ...args);
        }
    }

    function setState(newState) {
        state = Object.assign({}, state, newState);
        log('State updated:', state);
    }

    /**
     * Get page ID from URL and window.projectData
     * Format: "project-slug:page-slug"
     */
    function getPageId() {
        if (typeof window.projectData === 'object' && window.projectData.slug) {
            var pageSlug = window.location.pathname.split('/').pop() || 'index';
            return window.projectData.slug + ':' + pageSlug;
        }
        return null;
    }

    /**
     * Load checkbox state from localStorage
     */
    function loadState() {
        var pageId = getPageId();
        if (!pageId) return {};

        var stored = localStorage.getItem(CONFIG.storageKey);
        if (!stored) return {};

        try {
            var allState = JSON.parse(stored);
            return allState[pageId] || {};
        } catch (e) {
            log('Failed to parse localStorage:', e);
            return {};
        }
    }

    /**
     * Save checkbox state to localStorage
     */
    function saveState(pageId, checkboxStates) {
        var stored = localStorage.getItem(CONFIG.storageKey);
        var allState = {};

        try {
            if (stored) allState = JSON.parse(stored);
        } catch (e) {
            log('Failed to parse localStorage, overwriting:', e);
        }

        allState[pageId] = checkboxStates;
        try {
            localStorage.setItem(CONFIG.storageKey, JSON.stringify(allState));
        } catch (e) {
            log('Failed to save to localStorage:', e);
        }
    }

    // -- Private: Setup --
    function cacheDom() {
        $contentArea = window.jQuery(SELECTORS.contentArea);
    }

    function setup() {
        cacheDom();
        if (!$contentArea.length) {
            log('Content area not found');
            return false;
        }
        return true;
    }

    // -- Private: Events --
    function bindEvents() {
        var $ = window.jQuery;
        var pageId = getPageId();
        if (!pageId) return;

        $(document).on('change' + CONFIG.eventNamespace, SELECTORS.checkboxes, function () {
            var $checkboxes = $(SELECTORS.checkboxes);
            var newState = {};
            $checkboxes.each(function () {
                var $cb = $(this);
                var id = $cb.attr('id') || 'checkbox-' + $checkboxes.index($cb);
                newState[id] = $cb.is(':checked');
            });
            saveState(pageId, newState);
            log('Saved state:', newState);
        });
    }

    function unbindEvents() {
        window.jQuery(document).off(CONFIG.eventNamespace);
    }

    // -- Private: Enable Checkboxes --
    function enableCheckboxes() {
        var $ = window.jQuery;
        var pageId = getPageId();
        if (!pageId) {
            log('No page ID; skipping');
            return;
        }

        var $checkboxes = $(SELECTORS.checkboxes);
        if (!$checkboxes.length) {
            log('No checkboxes found');
            return;
        }

        var savedState = loadState();
        log('Found', $checkboxes.length, 'checkbox(es), saved state:', savedState);

        $checkboxes.each(function (idx) {
            var $checkbox = $(this);

            // Generate stable ID if missing (based on page + index)
            if (!$checkbox.attr('id')) {
                var pageId = getPageId();
                $checkbox.attr('id', pageId ? pageId.replace(/:/g, '-') + '-checkbox-' + idx : 'dc-checkbox-' + idx);
            }

            // Enable the checkbox
            $checkbox.prop('disabled', false);

            // Restore saved state
            if (savedState[$checkbox.attr('id')] !== undefined) {
                $checkbox.prop('checked', savedState[$checkbox.attr('id')]);
                log('Restored', $checkbox.attr('id'), '=', savedState[$checkbox.attr('id')]);
            }
        });

        setState({ checkboxCount: $checkboxes.length });
    }

    // -- Public: Init --
    function init() {
        var $ = window.jQuery;

        if (!$) {
            console.warn('[InteractiveContent] jQuery not available');
            return;
        }

        if (!setup()) return;
        if (state.initialized) return;

        enableCheckboxes();
        bindEvents();

        setState({ initialized: true });
        log('Initialized');
    }

    // -- Public: Destroy --
    function destroy() {
        if (!state.initialized) return;

        unbindEvents();
        setState({ initialized: false, checkboxCount: 0 });

        log('Destroyed');
    }

    // -- Public API --
    return { init: init, destroy: destroy };
})();

window.InteractiveContent = InteractiveContent;
