/**
 * ===========================================
 * CodeHighlight
 * ===========================================
 *
 * Runs hljs syntax highlighting and appends
 * a copy-to-clipboard button to each code block.
 *
 * @usage HTML
 *   <pre><code class="language-js">...</code></pre>
 *   (Standard markdown output — no data-* attrs needed)
 */

const CodeHighlight = (function () {
    'use strict';

    // -- Private: Config --
    const CONFIG = {
        debug: false,
        eventNamespace: '.codeHighlight',
        copyResetMs: 2000,
    };

    // -- Private: State --
    let state = {
        initialized: false,
    };

    // -- Private: Utils --
    function log(...args) {
        if (CONFIG.debug) console.log('[CodeHighlight]', ...args);
    }

    function setState(newState) {
        state = Object.assign({}, state, newState);
    }

    // -- Private: Core --
    function highlight() {
        if (typeof hljs !== 'undefined') {
            hljs.highlightAll();
            log('hljs applied');
        }
    }

    function addCopyButtons() {
        const $ = window.jQuery;

        $('pre code').each(function () {
            var $code = $(this);
            var $pre = $code.parent();

            if ($pre.find('[data-copy-btn]').length) return;

            var $btn = $('<button>')
                .attr('data-copy-btn', '')
                .addClass('copy-btn absolute top-2 right-2 px-2 py-1 text-xs bg-gray-700 text-gray-300 rounded hover:bg-gray-600 opacity-0 group-hover:opacity-100 transition-opacity')
                .text('Copy');

            $btn.on('click' + CONFIG.eventNamespace, function () {
                var code = $code.text();
                navigator.clipboard.writeText(code).then(function () {
                    $btn.text('Copied!');
                    setTimeout(function () { $btn.text('Copy'); }, CONFIG.copyResetMs);
                });
            });

            $pre.addClass('relative group').append($btn);
        });

        log('Copy buttons added');
    }

    // -- Public: Init --
    function init() {
        const $ = window.jQuery;
        if (!$) { console.warn('[CodeHighlight] jQuery not available'); return; }
        if (state.initialized) return;

        highlight();
        addCopyButtons();

        setState({ initialized: true });
        log('Initialized');
    }

    // -- Public: Destroy --
    function destroy() {
        if (!state.initialized) return;
        window.jQuery('[data-copy-btn]').off(CONFIG.eventNamespace);
        setState({ initialized: false });
        log('Destroyed');
    }

    // -- Public API --
    return { init: init, destroy: destroy };

})();

window.CodeHighlight = CodeHighlight;
