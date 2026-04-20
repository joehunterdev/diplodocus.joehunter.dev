/**
 * Main Application JavaScript
 * Entry point for all modules
 */

(function($) {
    'use strict';
    
    // Initialize all modules when DOM is ready
    $(document).ready(function() {
        App.init();
    });
    
    // Main App namespace
    window.App = {
        init: function() {
            Sidebar.init();
            Search.init();
            TOC.init();
            CodeHighlight.init();
            
            console.log('Documentation app initialized');
        }
    };
    
    /**
     * Sidebar Module
     */
    window.Sidebar = {
        $sidebar: null,
        $toggle: null,
        
        init: function() {
            this.$sidebar = $('#sidebar');
            this.$toggle = $('#sidebar-toggle');
            
            if (!this.$sidebar.length) return;
            
            this.bindEvents();
            this.restoreState();
        },
        
        bindEvents: function() {
            var self = this;
            
            this.$toggle.on('click', function(e) {
                e.preventDefault();
                self.toggle();
            });
            
            // Close sidebar on mobile when clicking outside
            $(document).on('click', function(e) {
                if (window.innerWidth < 1024) {
                    if (!$(e.target).closest('#sidebar, #sidebar-toggle').length) {
                        self.close();
                    }
                }
            });
        },
        
        toggle: function() {
            this.$sidebar.toggleClass('hidden');
            this.saveState();
        },
        
        open: function() {
            this.$sidebar.removeClass('hidden');
            this.saveState();
        },
        
        close: function() {
            this.$sidebar.addClass('hidden');
            this.saveState();
        },
        
        saveState: function() {
            var isHidden = this.$sidebar.hasClass('hidden');
            localStorage.setItem('sidebar-hidden', isHidden);
        },
        
        restoreState: function() {
            // Only restore on larger screens
            if (window.innerWidth >= 1024) {
                var isHidden = localStorage.getItem('sidebar-hidden') === 'true';
                if (isHidden) {
                    this.$sidebar.addClass('hidden');
                }
            }
        }
    };
    
    /**
     * Search Module
     */
    window.Search = {
        $input: null,
        $results: null,
        
        init: function() {
            this.$input = $('#sidebar-search');
            
            if (!this.$input.length) return;
            
            this.bindEvents();
        },
        
        bindEvents: function() {
            var self = this;
            var debounceTimer;
            
            this.$input.on('input', function() {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(function() {
                    self.search(self.$input.val());
                }, 300);
            });
            
            // Clear on escape
            this.$input.on('keydown', function(e) {
                if (e.key === 'Escape') {
                    self.$input.val('');
                    self.clearHighlights();
                }
            });
        },
        
        search: function(query) {
            if (!query || query.length < 2) {
                this.clearHighlights();
                return;
            }
            
            query = query.toLowerCase();
            
            // Highlight matching nav items
            $('nav a').each(function() {
                var $link = $(this);
                var text = $link.text().toLowerCase();
                
                if (text.indexOf(query) !== -1) {
                    $link.addClass('search-match bg-blue-900/30');
                } else {
                    $link.removeClass('search-match bg-blue-900/30');
                }
            });
        },
        
        clearHighlights: function() {
            $('nav a').removeClass('search-match bg-blue-900/30');
        }
    };
    
    /**
     * Table of Contents Module
     */
    window.TOC = {
        $links: null,
        $headings: null,
        
        init: function() {
            this.$links = $('.toc-link');
            
            if (!this.$links.length) return;
            
            this.$headings = $('article h2, article h3, article h4');
            
            this.bindEvents();
            this.setupScrollSpy();
        },
        
        bindEvents: function() {
            var self = this;
            
            // Smooth scroll to anchor
            this.$links.on('click', function(e) {
                e.preventDefault();
                var target = $(this).attr('href');
                var $target = $(target);
                
                if ($target.length) {
                    $('html, body').animate({
                        scrollTop: $target.offset().top - 100
                    }, 300);
                }
            });
        },
        
        setupScrollSpy: function() {
            var self = this;
            var $main = $('main');
            
            if (!$main.length) return;
            
            $main.on('scroll', function() {
                self.updateActiveLink();
            });
            
            // Initial update
            this.updateActiveLink();
        },
        
        updateActiveLink: function() {
            var self = this;
            var scrollTop = $('main').scrollTop();
            var activeId = null;
            
            this.$headings.each(function() {
                var $heading = $(this);
                var id = $heading.attr('id');
                
                if (id && $heading.position().top <= scrollTop + 150) {
                    activeId = id;
                }
            });
            
            // Update active state
            this.$links.removeClass('text-blue-400 font-medium');
            if (activeId) {
                this.$links.filter('[data-target="' + activeId + '"]').addClass('text-blue-400 font-medium');
            }
        }
    };
    
    /**
     * Code Highlight Module
     */
    window.CodeHighlight = {
        init: function() {
            if (typeof hljs !== 'undefined') {
                hljs.highlightAll();
                this.addCopyButtons();
            }
        },
        
        addCopyButtons: function() {
            $('pre code').each(function() {
                var $code = $(this);
                var $pre = $code.parent();
                
                // Skip if already has button
                if ($pre.find('.copy-btn').length) return;
                
                var $btn = $('<button>')
                    .addClass('copy-btn absolute top-2 right-2 px-2 py-1 text-xs bg-gray-700 text-gray-300 rounded hover:bg-gray-600 opacity-0 group-hover:opacity-100 transition-opacity')
                    .text('Copy')
                    .on('click', function() {
                        var code = $code.text();
                        navigator.clipboard.writeText(code).then(function() {
                            $btn.text('Copied!');
                            setTimeout(function() {
                                $btn.text('Copy');
                            }, 2000);
                        });
                    });
                
                $pre.addClass('relative group').append($btn);
            });
        }
    };
    
})(jQuery || { 
    // Minimal jQuery fallback
    ready: function(fn) { document.addEventListener('DOMContentLoaded', fn); },
    fn: function(selector) {
        var el = document.querySelector(selector);
        return {
            length: el ? 1 : 0,
            addClass: function(c) { el && el.classList.add(c); return this; },
            removeClass: function(c) { el && el.classList.remove(c); return this; },
            toggleClass: function(c) { el && el.classList.toggle(c); return this; },
            hasClass: function(c) { return el && el.classList.contains(c); },
            on: function(e, fn) { el && el.addEventListener(e, fn); return this; },
            val: function(v) { if (v === undefined) return el ? el.value : ''; el.value = v; return this; },
            text: function() { return el ? el.textContent : ''; },
            attr: function(a) { return el ? el.getAttribute(a) : null; }
        };
    }
});

// Make $ a no-op if jQuery isn't loaded
if (typeof jQuery === 'undefined') {
    window.$ = function(selector) {
        if (typeof selector === 'function') {
            document.addEventListener('DOMContentLoaded', selector);
            return;
        }
        var elements = document.querySelectorAll(selector);
        return {
            length: elements.length,
            each: function(fn) { elements.forEach(function(el, i) { fn.call(el, i, el); }); return this; },
            on: function(e, fn) { elements.forEach(function(el) { el.addEventListener(e, fn); }); return this; },
            addClass: function(c) { elements.forEach(function(el) { el.classList.add(c); }); return this; },
            removeClass: function(c) { elements.forEach(function(el) { el.classList.remove(c); }); return this; },
            toggleClass: function(c) { elements.forEach(function(el) { el.classList.toggle(c); }); return this; },
            hasClass: function(c) { return elements[0] && elements[0].classList.contains(c); },
            val: function(v) { 
                if (v === undefined) return elements[0] ? elements[0].value : ''; 
                elements.forEach(function(el) { el.value = v; }); 
                return this; 
            },
            text: function(v) { 
                if (v === undefined) return elements[0] ? elements[0].textContent : ''; 
                elements.forEach(function(el) { el.textContent = v; }); 
                return this; 
            },
            attr: function(a, v) { 
                if (v === undefined) return elements[0] ? elements[0].getAttribute(a) : null;
                elements.forEach(function(el) { el.setAttribute(a, v); });
                return this;
            },
            parent: function() { return $(elements[0] ? elements[0].parentElement : null); },
            find: function(s) { return $(elements[0] ? elements[0].querySelector(s) : null); },
            filter: function(s) {
                var filtered = Array.from(elements).filter(function(el) { return el.matches(s); });
                return $(filtered);
            },
            position: function() { 
                return elements[0] ? { top: elements[0].offsetTop, left: elements[0].offsetLeft } : { top: 0, left: 0 }; 
            },
            offset: function() {
                if (!elements[0]) return { top: 0, left: 0 };
                var rect = elements[0].getBoundingClientRect();
                return { top: rect.top + window.scrollY, left: rect.left + window.scrollX };
            },
            scrollTop: function() { return elements[0] ? elements[0].scrollTop : 0; },
            animate: function(props, duration) {
                if (props.scrollTop !== undefined && elements[0]) {
                    elements[0].scrollTo({ top: props.scrollTop, behavior: 'smooth' });
                }
                return this;
            },
            append: function(el) { 
                elements.forEach(function(parent) { 
                    if (el instanceof HTMLElement) parent.appendChild(el);
                    else if (el.get) parent.appendChild(el.get(0));
                }); 
                return this; 
            },
            get: function(i) { return elements[i]; },
            closest: function(s) {
                var el = elements[0] ? elements[0].closest(s) : null;
                return el ? $(el) : $([]);
            }
        };
    };
}
