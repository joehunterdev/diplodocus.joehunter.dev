/**
 * ===========================================
 * Search
 * ===========================================
 *
 * Search across all pages and headings in projectData.
 * Displays results in a dropdown panel below the search input.
 *
 * @usage HTML
 *   <input type="text" data-sidebar-search placeholder="Search...">
 */

const Search = (function () {
    'use strict';

    // -- Private: Config --
    const CONFIG = {
        debug: false,
        eventNamespace: '.search',
        debounceMs: 300,
        minLength: 2,
        maxResults: 20,
    };

    // -- Private: Selectors --
    const SELECTORS = {
        input: '[data-sidebar-search]',
        resultsPanel: '[data-search-results]',
    };

    // -- Private: State --
    let state = {
        initialized: false,
        query: '',
        results: [],
        allPages: [],
    };

    // -- Private: Cached DOM --
    let $input = null;
    let $resultsPanel = null;
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
        const $ = window.jQuery;
        $input = $(SELECTORS.input);
        $resultsPanel = $(SELECTORS.resultsPanel);
    }

    function setup() {
        cacheDom();
        // Create results panel if it doesn't exist
        if ($resultsPanel.length === 0) {
            const $ = window.jQuery;
            const panelHtml = '<div data-search-results style="display:none; position:fixed; max-height:320px; overflow-y:auto; background:var(--dc-bg-secondary); border:1px solid var(--dc-border-subtle); border-radius:6px; z-index:1000; box-shadow:0 4px 12px rgba(0,0,0,0.15); min-width:200px; max-width:320px;"></div>';
            $('body').append(panelHtml);
            $resultsPanel = $('[data-search-results]');
        }
        return $input.length > 0 && window.projectData;
    }

    // -- Private: Build search index from all pages --
    function buildAllPagesIndex() {
        const $ = window.jQuery;
        const pages = window.projectData && window.projectData.pages ? window.projectData.pages : [];
        const allPages = [];

        pages.forEach(function (page) {
            // Add page as searchable item
            allPages.push({
                type: 'page',
                text: page.displayName,
                project: window.projectData.slug,
                pageSlug: page.slug,
                headingId: null,
            });
        });

        // If current page has searchIndex, add headings
        if (window.searchIndex && Array.isArray(window.searchIndex)) {
            window.searchIndex.forEach(function (item) {
                if (item.type === 'heading') {
                    allPages.push({
                        type: 'heading',
                        text: item.text,
                        level: item.level,
                        project: window.projectData.slug,
                        pageSlug: null,  // Will fill from context
                        headingId: item.headingId,
                    });
                }
            });
        }

        return allPages;
    }

    // -- Private: Search function --
    function search(query) {
        const $ = window.jQuery;
        setState({ query: query });

        if (!query || query.length < CONFIG.minLength) {
            $resultsPanel.hide();
            setState({ results: [] });
            return;
        }

        const q = query.toLowerCase();
        const results = [];

        // Search all pages
        if (window.projectData && window.projectData.pages) {
            window.projectData.pages.forEach(function (page) {
                const text = page.displayName.toLowerCase();
                if (text.indexOf(q) !== -1 && results.length < CONFIG.maxResults) {
                    results.push({
                        type: 'page',
                        text: page.displayName,
                        project: window.projectData.slug,
                        pageSlug: page.slug,
                        headingId: null,
                    });
                }
            });
        }

        // Search current page headings
        if (window.searchIndex && Array.isArray(window.searchIndex)) {
            window.searchIndex.forEach(function (item) {
                const text = item.text.toLowerCase();
                if (text.indexOf(q) !== -1 && results.length < CONFIG.maxResults) {
                    results.push({
                        type: item.type,
                        text: item.text,
                        level: item.level,
                        project: window.projectData ? window.projectData.slug : null,
                        pageSlug: window.projectData && window.projectData.pages && window.projectData.pages.length > 0 ? window.projectData.pages[0].slug : null,
                        headingId: item.headingId,
                    });
                }
            });
        }

        setState({ results: results });
        renderResults(results);
        log('Searched:', query, 'found:', results.length);
    }

    // -- Private: Position results panel below input --
    function positionPanel() {
        var inputRect = $input[0].getBoundingClientRect();
        $resultsPanel.css({
            left: inputRect.left + 'px',
            top: (inputRect.bottom + 8) + 'px',
            width: inputRect.width + 'px'
        });
    }

    // -- Private: Render results panel --
    function renderResults(results) {
        const $ = window.jQuery;

        if (results.length === 0) {
            $resultsPanel.html('<div style="padding:1.5rem; text-align:center; color:var(--dc-text-secondary); font-size:0.875rem;">No results found</div>');
            positionPanel();
            $resultsPanel.show();
            return;
        }

        let html = '<div style="padding:0.5rem 0;">';
        results.forEach(function (result) {
            let href = '';
            let label = '';
            let icon = '';
            let typeLabel = '';

            if (result.type === 'page') {
                icon = '📄';
                label = result.text;
                typeLabel = 'Page';
                href = '/?project=' + encodeURIComponent(result.project) + '&page=' + encodeURIComponent(result.pageSlug);
            } else if (result.type === 'heading') {
                icon = result.level === 2 ? '##' : '###';
                label = result.text;
                typeLabel = 'Heading H' + result.level;
                // Link to heading on current page
                href = (window.location.pathname || '/') + (result.headingId ? '#' + result.headingId : '');
            } else if (result.type === 'title') {
                icon = '📋';
                label = result.text;
                typeLabel = 'Title';
                href = (window.location.pathname || '/');
            }

            html += '<a href="' + href + '" style="display:flex; align-items:center; gap:0.5rem; padding:0.5rem 0.75rem; cursor:pointer; color:var(--dc-text-primary); text-decoration:none; border-bottom:1px solid var(--dc-border-subtle); transition:background-color 0.15s ease; font-size:0.85rem;" onmouseover="this.style.backgroundColor=\'rgba(255,255,255,0.06)\'" onmouseout="this.style.backgroundColor=\'transparent\'">';
            html += '<span style="font-size:0.9rem; flex-shrink:0;">' + icon + '</span>';
            html += '<span style="flex:1; min-width:0; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; font-weight:500;">' + label.replace(/</g, '&lt;').replace(/>/g, '&gt;') + '</span>';
            html += '<span style="font-size:0.7rem; color:var(--dc-text-secondary); flex-shrink:0;">' + typeLabel + '</span>';
            html += '</a>';
        });
        html += '</div>';

        $resultsPanel.html(html);
        positionPanel();
        $resultsPanel.show();
    }

    function clearResults() {
        $resultsPanel.hide();
        setState({ query: '', results: [] });
    }

    // -- Private: Events --
    function bindEvents() {
        const $ = window.jQuery;

        // Input event with debounce
        $input.on('input' + CONFIG.eventNamespace, function () {
            clearTimeout(debounceTimer);
            const val = $(this).val();
            debounceTimer = setTimeout(function () {
                search(val);
            }, CONFIG.debounceMs);
        });

        // Clear on Escape
        $input.on('keydown' + CONFIG.eventNamespace, function (e) {
            if (e.key === 'Escape') {
                $(this).val('');
                clearResults();
            }
        });

        // Close results when clicking outside
        $(document).on('click' + CONFIG.eventNamespace, function (e) {
            const $target = $(e.target);
            if (!$target.closest(SELECTORS.input).length && !$target.closest(SELECTORS.resultsPanel).length) {
                clearResults();
            }
        });
    }

    function unbindEvents() {
        if ($input) $input.off(CONFIG.eventNamespace);
        $(document).off(CONFIG.eventNamespace);
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
