/**
 * jQuery Compatibility Layer
 * Sets window.$ and window.jQuery if real jQuery is not loaded.
 * Provides enough of the jQuery API to support the module pattern used here.
 */

(function () {
    'use strict';

    if (typeof window.jQuery !== 'undefined') return;

    function $(selector, context) {
        if (typeof selector === 'function') {
            document.addEventListener('DOMContentLoaded', selector);
            return;
        }

        var root = context instanceof HTMLElement ? context : document;
        var elements;

        if (selector instanceof HTMLElement || selector instanceof Window || selector instanceof Document) {
            elements = [selector];
        } else if (Array.isArray(selector)) {
            elements = selector.filter(Boolean);
        } else if (typeof selector === 'string') {
            elements = Array.from(root.querySelectorAll(selector));
        } else {
            elements = [];
        }

        var api = {
            length: elements.length,

            each: function (fn) {
                elements.forEach(function (el, i) { fn.call(el, i, el); });
                return api;
            },

            on: function (events, selectorOrFn, fn) {
                var delegateSelector = typeof selectorOrFn === 'string' ? selectorOrFn : null;
                var handler = delegateSelector ? fn : selectorOrFn;
                var eventNames = events.split(' ').map(function (e) { return e.split('.')[0]; });

                elements.forEach(function (el) {
                    eventNames.forEach(function (ev) {
                        if (!ev) return;
                        var wrapped = delegateSelector
                            ? function (e) {
                                var target = e.target.closest(delegateSelector);
                                if (target && el.contains(target)) {
                                    handler.call(target, e);
                                }
                            }
                            : function (e) { handler.call(el, e); };

                        el._jqHandlers = el._jqHandlers || {};
                        var key = events + '|' + (delegateSelector || '');
                        el._jqHandlers[key] = el._jqHandlers[key] || [];
                        el._jqHandlers[key].push(wrapped);
                        el.addEventListener(ev, wrapped);
                    });
                });
                return api;
            },

            off: function (events) {
                var ns = events || '';
                elements.forEach(function (el) {
                    if (!el._jqHandlers) return;
                    Object.keys(el._jqHandlers).forEach(function (key) {
                        if (!ns || key.indexOf(ns) !== -1) {
                            var eventName = key.split('|')[0].split('.')[0];
                            el._jqHandlers[key].forEach(function (h) {
                                el.removeEventListener(eventName, h);
                            });
                            delete el._jqHandlers[key];
                        }
                    });
                });
                return api;
            },

            addClass: function (c) {
                elements.forEach(function (el) { c.split(' ').forEach(function(cls){ if(cls) el.classList.add(cls); }); });
                return api;
            },

            removeClass: function (c) {
                elements.forEach(function (el) { c.split(' ').forEach(function(cls){ if(cls) el.classList.remove(cls); }); });
                return api;
            },

            toggleClass: function (c) {
                elements.forEach(function (el) { el.classList.toggle(c); });
                return api;
            },

            hasClass: function (c) {
                return elements[0] ? elements[0].classList.contains(c) : false;
            },

            val: function (v) {
                if (v === undefined) return elements[0] ? elements[0].value : '';
                elements.forEach(function (el) { el.value = v; });
                return api;
            },

            text: function (v) {
                if (v === undefined) return elements[0] ? elements[0].textContent : '';
                elements.forEach(function (el) { el.textContent = v; });
                return api;
            },

            html: function (v) {
                if (v === undefined) return elements[0] ? elements[0].innerHTML : '';
                elements.forEach(function (el) { el.innerHTML = v; });
                return api;
            },

            attr: function (a, v) {
                if (v === undefined) return elements[0] ? elements[0].getAttribute(a) : null;
                elements.forEach(function (el) { el.setAttribute(a, v); });
                return api;
            },

            data: function (key) {
                return elements[0] ? elements[0].dataset[key] : undefined;
            },

            css: function (prop, val) {
                if (val === undefined) return elements[0] ? getComputedStyle(elements[0])[prop] : '';
                elements.forEach(function (el) { el.style[prop] = val; });
                return api;
            },

            show: function () {
                elements.forEach(function (el) { el.style.display = ''; });
                return api;
            },

            hide: function () {
                elements.forEach(function (el) { el.style.display = 'none'; });
                return api;
            },

            parent: function () {
                return $(elements[0] ? elements[0].parentElement : []);
            },

            closest: function (sel) {
                var el = elements[0] ? elements[0].closest(sel) : null;
                return $(el ? [el] : []);
            },

            find: function (sel) {
                var results = [];
                elements.forEach(function (el) {
                    results = results.concat(Array.from(el.querySelectorAll(sel)));
                });
                return $(results);
            },

            filter: function (sel) {
                return $(elements.filter(function (el) { return el.matches(sel); }));
            },

            append: function (child) {
                elements.forEach(function (el) {
                    if (child && child.length !== undefined) {
                        child.each(function () { el.appendChild(this); });
                    } else if (child instanceof HTMLElement) {
                        el.appendChild(child);
                    }
                });
                return api;
            },

            position: function () {
                return elements[0] ? { top: elements[0].offsetTop, left: elements[0].offsetLeft } : { top: 0, left: 0 };
            },

            offset: function () {
                if (!elements[0]) return { top: 0, left: 0 };
                var rect = elements[0].getBoundingClientRect();
                return { top: rect.top + window.scrollY, left: rect.left + window.scrollX };
            },

            scrollTop: function () {
                return elements[0] ? elements[0].scrollTop : 0;
            },

            animate: function (props) {
                if (props.scrollTop !== undefined && elements[0]) {
                    elements[0].scrollTo({ top: props.scrollTop, behavior: 'smooth' });
                }
                return api;
            },

            get: function (i) { return elements[i]; },

            is: function (sel) {
                return elements[0] ? elements[0].matches(sel) : false;
            },

            contains: function (el) {
                return elements[0] ? elements[0].contains(el instanceof HTMLElement ? el : el.get(0)) : false;
            },
        };

        return api;
    }

    // Factory for creating new elements: $('<button>')
    var originalDollar = $;
    window.$ = function (selector, context) {
        if (typeof selector === 'string' && selector[0] === '<') {
            var div = document.createElement('div');
            div.innerHTML = selector.trim();
            return originalDollar(Array.from(div.children));
        }
        return originalDollar(selector, context);
    };

    window.jQuery = window.$;

})();
