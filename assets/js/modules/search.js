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
        return $input.length > 0 && $resultsPanel.length > 0;
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
            $resultsPanel.attr('hidden', '');
            $resultsPanel.empty();
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

        // Search all headings across all project pages
        if (window.searchIndex && Array.isArray(window.searchIndex)) {
            window.searchIndex.forEach(function (item) {
                const text = item.text.toLowerCase();
                if (text.indexOf(q) !== -1 && results.length < CONFIG.maxResults) {
                    results.push({
                        type: item.type,
                        text: item.text,
                        level: item.level,
                        project: window.projectData ? window.projectData.slug : null,
                        pageSlug: item.pageSlug || null,
                        headingId: item.headingId,
                    });
                }
            });
        }

        setState({ results: results });
        renderResults(results);
        log('Searched:', query, 'found:', results.length);
    }

    // -- Private: Render results panel --
    function renderResults(results) {
        const $ = window.jQuery;

        if (results.length === 0) {
            $resultsPanel.html('<div class="dc-search-empty">No results found</div>');
            $resultsPanel.removeAttr('hidden');
            return;
        }

        let html = '';
        results.forEach(function (result) {
            let href = '';
            let label = '';
            let meta = '';

            if (result.type === 'page') {
                label = result.text;
                meta = 'Page';
                href = '/' + encodeURIComponent(result.project) + '/' + encodeURIComponent(result.pageSlug);
            } else if (result.type === 'heading') {
                label = result.text;
                meta = 'H' + result.level;
                href = '/' + encodeURIComponent(result.project) + '/' + encodeURIComponent(result.pageSlug) + (result.headingId ? '#' + result.headingId : '');
            } else if (result.type === 'title') {
                label = result.text;
                meta = 'Title';
                href = '/' + encodeURIComponent(result.project) + '/' + encodeURIComponent(result.pageSlug);
            }

            html += '<a href="' + href + '" class="dc-search-hit">';
            html += '<span class="dc-search-hit-label">' + label.replace(/</g, '&lt;').replace(/>/g, '&gt;') + '</span>';
            html += '<span class="dc-search-hit-meta">' + meta + '</span>';
            html += '</a>';
        });

        $resultsPanel.html(html);
        $resultsPanel.removeAttr('hidden');
    }

    function clearResults() {
        $resultsPanel.attr('hidden', '');
        $resultsPanel.empty();
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

export default Search;
