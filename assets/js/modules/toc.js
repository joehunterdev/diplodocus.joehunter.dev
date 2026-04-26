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
    let isWindowScroll = false;  // Track whether we're scrolling the window or an element

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

        // Resolve scroll container. If [data-main-scroll] is not actually
        // scrollable (overflow-y: visible), fall back to window scrolling so
        // scrollTop reads and animations land on the real scroller.
        var $candidate = $(SELECTORS.scrollContainer);
        var resolved = resolveScroller($candidate);
        $scrollContainer = resolved.$element;
        isWindowScroll = resolved.isWindow;

        // Headings come from rendered markdown — use semantic selectors
        $headings = $(SELECTORS.article + ' h2, ' + SELECTORS.article + ' h3, ' + SELECTORS.article + ' h4');
    }

    function resolveScroller($candidate) {
        const $ = window.jQuery;
        if ($candidate.length) {
            var el = $candidate[0];
            if (!(el instanceof Element)) {
                return { $element: $('html, body'), isWindow: true };
            }
            var style = window.getComputedStyle(el);
            var isScrollable = /(auto|scroll|overlay)/.test(style.overflowY) &&
                el.scrollHeight > el.clientHeight;
            if (isScrollable) {
                return { $element: $candidate, isWindow: false };
            }
        }
        // Fall back to the window scroller. Use html,body for cross-browser animate target.
        return { $element: $('html, body'), isWindow: true };
    }

    function getScrollTop() {
        // Works whether we're scrolling an element or the window.
        if (!isWindowScroll) {
            return $scrollContainer.scrollTop();
        }
        return window.scrollY || document.documentElement.scrollTop || document.body.scrollTop || 0;
    }

    function setup() {
        cacheDom();
        return $links.length > 0;
    }

    // -- Private: Core --
    function updateActiveLink() {
        const $ = window.jQuery;
        var scrollTop = getScrollTop();
        var activeId = null;

        $headings.each(function () {
            var $h = $(this);
            var id = $h.attr('id');

            if (!id) return;

            // Use getBoundingClientRect relative to the viewport
            var rectTop = this.getBoundingClientRect().top;

            // For window scrolling, rectTop is measured from viewport top
            // For element scrolling, we need position relative to container top
            var topRelative = isWindowScroll
                ? rectTop
                : this.offsetTop - $scrollContainer.scrollTop();

            // Check if heading is above or near the scroll position (within scrollOffset)
            if (topRelative <= CONFIG.scrollOffset) {
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
            if (!target || target.charAt(0) !== '#') return;
            var $target = $(target);
            if (!$target.length) return;

            var targetTop;
            if (isWindowScroll) {
                // Window-based scroller: use offset() to get document-relative position
                targetTop = $target.offset().top - CONFIG.scrollOffset;
            } else {
                // Element-based scroller: use position() relative to container
                targetTop = $target.position().top - CONFIG.scrollOffset;
            }

            $scrollContainer.animate({ scrollTop: Math.max(0, targetTop) }, 300);

            // Update hash without jumping (since we preventDefault'd).
            if (history.replaceState) {
                history.replaceState(null, '', target);
            }
        });

        // Listen to the right scroll source
        var $scrollSrc = isWindowScroll ? $(window) : $scrollContainer;
        $scrollSrc.on('scroll' + CONFIG.eventNamespace, function () {
            updateActiveLink();
        });
    }

    function unbindEvents() {
        const $ = window.jQuery;
        if ($links) $links.off(CONFIG.eventNamespace);
        if ($scrollContainer) $scrollContainer.off(CONFIG.eventNamespace);
        $(window).off(CONFIG.eventNamespace);
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

export default TOC;
