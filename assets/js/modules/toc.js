/**
 * ===========================================
 * TOC (Table of Contents)
 * ===========================================
 *
 * Smooth-scroll to headings via TOC links.
 * Scroll spy highlights the active TOC link.
 *
 * @usage HTML
 *   <a data-toc-link data-target="heading-id" href="#heading-id">...</a>
 *   <main data-main-scroll>
 *     <article data-content-article>
 *       <h2 id="heading-id">...</h2>
 *     </article>
 *   </main>
 */

const TOC = (function () {
    'use strict';

    // -- Private: Config --
    const CONFIG = {
        debug: false,
        eventNamespace: '.toc',
        scrollOffset: 100,
        activeClass: 'is-active',
    };

    // -- Private: Selectors --
    const SELECTORS = {
        links: '[data-toc-link]',
        article: '[data-content-article]',
        scrollContainer: '[data-main-scroll]',
    };

    // -- Private: State --
    let state = {
        initialized: false,
        activeId: null,
    };

    // -- Private: Cached DOM --
    let $links = null;
    let $headings = null;
    let $scrollContainer = null;

    // -- Private: Utils --
    function log(...args) {
        if (CONFIG.debug) console.log('[TOC]', ...args);
    }

    function setState(newState) {
        state = Object.assign({}, state, newState);
    }

    // -- Private: Setup --
    function cacheDom() {
        const $ = window.jQuery;
        $links = $(SELECTORS.links);
        $scrollContainer = $(SELECTORS.scrollContainer);
        // Headings come from rendered markdown — use semantic selectors
        $headings = $(SELECTORS.article + ' h2, ' + SELECTORS.article + ' h3, ' + SELECTORS.article + ' h4');
    }

    function setup() {
        cacheDom();
        return $links.length > 0;
    }

    // -- Private: Core --
    function updateActiveLink() {
        const $ = window.jQuery;
        var scrollTop = $scrollContainer.scrollTop();
        var activeId = null;

        $headings.each(function () {
            var $h = $(this);
            var id = $h.attr('id');
            if (id && $h.position().top <= scrollTop + CONFIG.scrollOffset) {
                activeId = id;
            }
        });

        if (activeId !== state.activeId) {
            $links.removeClass(CONFIG.activeClass);
            if (activeId) {
                $links.filter('[data-target="' + activeId + '"]').addClass(CONFIG.activeClass);
            }
            setState({ activeId: activeId });
        }
    }

    // -- Private: Events --
    function bindEvents() {
        const $ = window.jQuery;

        $links.on('click' + CONFIG.eventNamespace, function (e) {
            e.preventDefault();
            var target = $(this).attr('href');
            var $target = $(target);
            if ($target.length) {
                $scrollContainer.animate({
                    scrollTop: $target.offset().top + $scrollContainer.scrollTop() - CONFIG.scrollOffset,
                });
            }
        });

        $scrollContainer.on('scroll' + CONFIG.eventNamespace, function () {
            updateActiveLink();
        });
    }

    function unbindEvents() {
        const $ = window.jQuery;
        if ($links) $links.off(CONFIG.eventNamespace);
        if ($scrollContainer) $scrollContainer.off(CONFIG.eventNamespace);
    }

    // -- Public: Init --
    function init() {
        const $ = window.jQuery;
        if (!$) { console.warn('[TOC] jQuery not available'); return; }
        if (!setup()) return;
        if (state.initialized) return;

        bindEvents();
        updateActiveLink();

        setState({ initialized: true });
        log('Initialized');
    }

    // -- Public: Destroy --
    function destroy() {
        if (!state.initialized) return;
        unbindEvents();
        setState({ initialized: false, activeId: null });
        log('Destroyed');
    }

    // -- Public API --
    return { init: init, destroy: destroy };

})();

window.TOC = TOC;
