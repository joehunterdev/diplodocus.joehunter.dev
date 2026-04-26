/**
 * ===========================================
 * Mermaid Diagrams
 * ===========================================
 *
 * Initialises mermaid.js and renders any
 * <pre><code class="language-mermaid">…</code></pre>
 * blocks inside [data-content-article] as inline SVG.
 *
 * @usage HTML
 *   <pre><code class="language-mermaid">
 *     flowchart TD
 *       A --> B
 *   </code></pre>
 *
 *   Parsedown emits exactly this structure for fenced ```mermaid blocks.
 */

const MermaidDiagrams = (function () {
    'use strict';

    const CONFIG = {
        debug: false,
        eventNamespace: '.mermaid',
        theme: 'dark',
        sourceSelector: '[data-content-article] pre code.language-mermaid',
        renderedClass: 'dc-mermaid',
    };

    let state = {
        initialized: false,
        rendered: 0,
    };

    function log() {
        if (CONFIG.debug) {
            var args = Array.prototype.slice.call(arguments);
            args.unshift('[Mermaid]');
            console.log.apply(console, args);
        }
    }

    function setState(newState) {
        state = Object.assign({}, state, newState);
    }

    function hasBlocks() {
        return document.querySelectorAll(CONFIG.sourceSelector).length > 0;
    }

    function renderAll() {
        if (typeof window.mermaid === 'undefined') {
            log('mermaid global missing — vendor file likely failed to load');
            return;
        }

        var blocks = document.querySelectorAll(CONFIG.sourceSelector);
        if (!blocks.length) return;

        window.mermaid.initialize({
            startOnLoad: false,
            theme: CONFIG.theme,
            securityLevel: 'strict',
            fontFamily: 'inherit',
        });

        var rendered = 0;

        blocks.forEach(function (codeEl, idx) {
            var source = codeEl.textContent.trim();
            var id = 'dc-mermaid-' + Date.now() + '-' + idx;
            var wrapper = document.createElement('div');
            wrapper.className = CONFIG.renderedClass;

            try {
                window.mermaid.render(id, source).then(function (result) {
                    wrapper.innerHTML = result.svg;
                    var pre = codeEl.closest('pre');
                    if (pre && pre.parentNode) {
                        pre.parentNode.replaceChild(wrapper, pre);
                        rendered++;
                        setState({ rendered: rendered });
                        log('Rendered block', idx);
                    }
                }).catch(function (err) {
                    log('render failed for block', idx, err);
                    wrapper.innerHTML = '<pre class="dc-mermaid-error">Mermaid error: '
                        + (err.message || String(err)) + '</pre>';
                    var pre = codeEl.closest('pre');
                    if (pre && pre.parentNode) {
                        pre.parentNode.replaceChild(wrapper, pre);
                    }
                });
            } catch (err) {
                log('exception during render', err);
            }
        });

        log('Rendering ' + blocks.length + ' block(s)');
    }

    function init() {
        if (state.initialized) return;
        if (!hasBlocks()) return;
        renderAll();
        setState({ initialized: true });
        log('Initialized');
    }

    function destroy() {
        setState({ initialized: false, rendered: 0 });
        log('Destroyed');
    }

    return { init: init, destroy: destroy };

})();

window.MermaidDiagrams = MermaidDiagrams;
