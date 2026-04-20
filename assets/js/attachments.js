/**
 * Attachment Gallery Module
 * Handles image lightbox, CSV/JSON preview, HTML iframe rendering
 */

(function() {
    'use strict';
    
    window.AttachmentGallery = {
        lightbox: null,
        currentImages: [],
        currentIndex: 0,
        
        init: function() {
            this.lightbox = document.getElementById('attachment-lightbox');
            if (!this.lightbox) return;
            
            this.bindEvents();
            this.initTabs();
            this.initDataToggles();
            this.initIframeToggles();
        },
        
        bindEvents: function() {
            var self = this;
            
            // Image cards click - open lightbox (but not if clicking on tag button)
            document.querySelectorAll('.image-card').forEach(function(card, index) {
                card.addEventListener('click', function(e) {
                    // Don't open lightbox if clicking on tag button
                    if (e.target.closest('.tag-attachment')) {
                        return;
                    }
                    self.openLightbox(index);
                });
            });
            
            // Collect all images
            document.querySelectorAll('.image-card').forEach(function(card) {
                self.currentImages.push({
                    src: card.dataset.src,
                    name: card.dataset.name
                });
            });
            
            // Lightbox controls
            if (this.lightbox) {
                this.lightbox.querySelector('.lightbox-close').addEventListener('click', function() {
                    self.closeLightbox();
                });
                
                this.lightbox.querySelector('.lightbox-prev').addEventListener('click', function() {
                    self.prevImage();
                });
                
                this.lightbox.querySelector('.lightbox-next').addEventListener('click', function() {
                    self.nextImage();
                });
                
                // Close on background click
                this.lightbox.addEventListener('click', function(e) {
                    if (e.target === self.lightbox) {
                        self.closeLightbox();
                    }
                });
                
                // Keyboard navigation
                document.addEventListener('keydown', function(e) {
                    if (!self.lightbox.classList.contains('hidden')) {
                        if (e.key === 'Escape') self.closeLightbox();
                        if (e.key === 'ArrowLeft') self.prevImage();
                        if (e.key === 'ArrowRight') self.nextImage();
                    }
                });
            }
            
            // Tag attachment popover logic
            console.log('Setting up tag handlers for', document.querySelectorAll('.tag-attachment').length, 'buttons');
            document.querySelectorAll('.tag-attachment').forEach(function(btn) {
                btn.addEventListener('click', function(e) {
                    console.log('Tag button clicked!', btn.dataset.attachment);
                    e.preventDefault();
                    e.stopPropagation();
                    
                    var popover = document.getElementById('tagAttachmentPopover');
                    var content = document.getElementById('tagPopoverContent');
                    if (!popover || !content) {
                        console.error('Tag popover not found');
                        return;
                    }
                    
                    // Get the attachment card (parent of the button)
                    var card = btn.closest('.attachment-card') || btn.parentElement;
                    var rect = card.getBoundingClientRect();
                    
                    // Position popover below and centered on the card
                    var popoverWidth = 256;
                    var top = rect.bottom + 8;
                    var left = rect.left + (rect.width / 2) - (popoverWidth / 2);
                    
                    // Keep within viewport horizontally
                    if (left < 10) left = 10;
                    if (left + popoverWidth > window.innerWidth - 10) {
                        left = window.innerWidth - popoverWidth - 10;
                    }
                    
                    // If popover would go below viewport, show it above the card
                    if (top + 200 > window.innerHeight) {
                        top = rect.top - 210;
                    }
                    
                    popover.style.top = top + 'px';
                    popover.style.left = left + 'px';
                    
                    popover.style.display = 'block';
                    content.innerHTML = '<p class="text-gray-500 text-xs">Loading...</p>';
                    
                    // Get current project
                    var project = document.body.dataset.project || '';
                    
                    // Load pages with sections - use current URL with new query params
                    var currentUrl = window.location.origin + window.location.pathname;
                    fetch(currentUrl + '?action=attachment-pages&project=' + encodeURIComponent(project))
                        .then(function(r) { return r.json(); })
                        .then(function(pages) {
                            var html = '<form id="tagForm" class="space-y-2">';
                            html += '<input type="hidden" name="attachment" value="' + btn.dataset.attachment + '">';
                            
                            // Page dropdown
                            html += '<div>';
                            html += '<label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Page</label>';
                            html += '<select name="page" id="tagPageSelect" class="w-full p-1.5 text-xs rounded border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">';
                            pages.forEach(function(page) {
                                html += '<option value="' + page.slug + '" data-sections=\'' + JSON.stringify(page.sections || []) + '\'>' + page.name + '</option>';
                            });
                            html += '</select>';
                            html += '</div>';
                            
                            // Section dropdown
                            html += '<div>';
                            html += '<label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Section</label>';
                            html += '<select name="section" id="tagSectionSelect" class="w-full p-1.5 text-xs rounded border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">';
                            html += '<option value="">-- Optional --</option>';
                            html += '</select>';
                            html += '</div>';
                            
                            html += '<button type="submit" class="w-full py-1.5 text-xs bg-blue-600 text-white rounded hover:bg-blue-700 transition-colors font-medium">Tag</button>';
                            html += '</form>';
                            
                            content.innerHTML = html;
                            
                            // Update sections when page changes
                            var pageSelect = document.getElementById('tagPageSelect');
                            var sectionSelect = document.getElementById('tagSectionSelect');
                            
                            function updateSections() {
                                var selected = pageSelect.options[pageSelect.selectedIndex];
                                var sections = JSON.parse(selected.dataset.sections || '[]');
                                sectionSelect.innerHTML = '<option value="">-- Optional --</option>';
                                sections.forEach(function(sec) {
                                    sectionSelect.innerHTML += '<option value="' + sec + '">' + sec + '</option>';
                                });
                            }
                            
                            pageSelect.addEventListener('change', updateSections);
                            updateSections();
                            
                            document.getElementById('tagForm').onsubmit = function(ev) {
                                ev.preventDefault();
                                var form = ev.target;
                                fetch(currentUrl + '?action=tag-attachment', {
                                    method: 'POST',
                                    headers: { 'Content-Type': 'application/json' },
                                    body: JSON.stringify({
                                        attachment: form.attachment.value,
                                        page: form.page.value,
                                        section: form.section.value
                                    })
                                })
                                .then(function(r) { return r.json(); })
                                .then(function(resp) {
                                    if (resp.success) {
                                        content.innerHTML = '<p class="text-green-600 text-xs text-center py-2">✓ Tagged!</p>';
                                        setTimeout(function() { popover.style.display = 'none'; }, 500);
                                    } else {
                                        content.innerHTML += '<p class="text-red-500 text-xs mt-1">Error</p>';
                                    }
                                });
                            };
                        });
                });
            });
            
            // Close popover
            var closePopoverBtn = document.getElementById('closeTagPopover');
            if (closePopoverBtn) {
                closePopoverBtn.addEventListener('click', function() {
                    document.getElementById('tagAttachmentPopover').style.display = 'none';
                });
            }
            
            // Close popover when clicking outside
            document.addEventListener('click', function(e) {
                var popover = document.getElementById('tagAttachmentPopover');
                if (popover && popover.style.display !== 'none') {
                    if (!popover.contains(e.target) && !e.target.closest('.tag-attachment')) {
                        popover.style.display = 'none';
                    }
                }
            });
            
            // Remove tag buttons
            document.querySelectorAll('.remove-tag').forEach(function(btn) {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    var attachment = this.dataset.attachment;
                    var page = this.dataset.page;
                    var section = this.dataset.section || '';
                    var tagElement = this.closest('.tagged-attachment');
                    
                    if (!confirm('Remove tag for "' + attachment + '"?')) return;
                    
                    var currentUrl = window.location.origin + window.location.pathname;
                    fetch(currentUrl + '?action=tag-attachment', {
                        method: 'DELETE',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            attachment: attachment,
                            page: page,
                            section: section
                        })
                    })
                    .then(function(r) { return r.json(); })
                    .then(function(resp) {
                        if (resp.success) {
                            tagElement.style.opacity = '0';
                            tagElement.style.transform = 'translateX(20px)';
                            setTimeout(function() {
                                tagElement.remove();
                            }, 300);
                        } else {
                            alert('Error removing tag: ' + (resp.error || 'Unknown'));
                        }
                    });
                });
            });
        },
        
        initTabs: function() {
            var self = this;
            var tabs = document.querySelectorAll('.attachment-tab');
            var sections = document.querySelectorAll('.attachment-section');
            
            tabs.forEach(function(tab) {
                tab.addEventListener('click', function() {
                    var targetTab = this.dataset.tab;
                    
                    // Update tab styles
                    tabs.forEach(function(t) {
                        t.classList.remove('active', 'border-blue-500', 'text-blue-600', 'dark:text-blue-400');
                        t.classList.add('border-transparent', 'text-gray-500', 'dark:text-gray-400');
                    });
                    this.classList.add('active', 'border-blue-500', 'text-blue-600', 'dark:text-blue-400');
                    this.classList.remove('border-transparent', 'text-gray-500', 'dark:text-gray-400');
                    
                    // Show/hide sections
                    sections.forEach(function(section) {
                        if (targetTab === 'all') {
                            section.classList.remove('hidden');
                        } else {
                            if (section.dataset.type === targetTab) {
                                section.classList.remove('hidden');
                            } else {
                                section.classList.add('hidden');
                            }
                        }
                    });
                });
            });
        },
        
        initDataToggles: function() {
            var self = this;
            
            document.querySelectorAll('.data-toggle').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    var card = this.closest('.data-card');
                    var preview = card.querySelector('.data-preview');
                    var file = this.dataset.file;
                    var type = this.dataset.type;
                    var project = this.dataset.project;
                    
                    if (preview.classList.contains('hidden')) {
                        // Load and show
                        self.loadDataFile(project, file, type, preview);
                        preview.classList.remove('hidden');
                        this.textContent = 'Hide';
                    } else {
                        preview.classList.add('hidden');
                        this.textContent = 'View';
                    }
                });
            });
        },
        
        loadDataFile: function(project, file, type, container) {
            container.innerHTML = '<div class="text-center py-4"><span class="text-gray-500">Loading...</span></div>';
            
            // Build URL using current page as base, adding our action
            var url = 'index.php?action=attachment-data&project=' + encodeURIComponent(project) + '&file=' + encodeURIComponent(file) + '&type=' + type;
            
            console.log('Loading data from:', url); // Debug
            console.log('Project:', project, 'File:', file, 'Type:', type); // Debug
            
            fetch(url)
                .then(function(response) {
                    console.log('Response status:', response.status);
                    console.log('Response headers:', response.headers.get('content-type'));
                    return response.text(); // Get as text first to debug
                })
                .then(function(text) {
                    console.log('Response text (first 200 chars):', text.substring(0, 200));
                    try {
                        var data = JSON.parse(text);
                        if (data.error) {
                            container.innerHTML = '<div class="text-red-500 p-4">' + data.error + '</div>';
                            return;
                        }
                        
                        if (type === 'csv') {
                            container.innerHTML = AttachmentGallery.renderCsvTable(data);
                        } else if (type === 'json') {
                            container.innerHTML = AttachmentGallery.renderJsonView(data);
                        }
                    } catch (e) {
                        console.error('JSON parse error:', e);
                        container.innerHTML = '<div class="text-red-500 p-4">Error: Response is not valid JSON. Check console.</div>';
                    }
                })
                .catch(function(err) {
                    container.innerHTML = '<div class="text-red-500 p-4">Error loading file: ' + err.message + '</div>';
                });
        },
        
        renderCsvTable: function(data) {
            if (!data.headers || !data.data) return '<div class="text-gray-500">No data</div>';
            
            var html = '<div class="overflow-x-auto">';
            html += '<table class="w-full text-sm">';
            html += '<thead><tr class="bg-gray-200 dark:bg-gray-700">';
            data.headers.forEach(function(h) {
                html += '<th class="px-3 py-2 text-left font-medium text-gray-700 dark:text-gray-200">' + AttachmentGallery.escapeHtml(h) + '</th>';
            });
            html += '</tr></thead><tbody>';
            
            data.data.forEach(function(row, i) {
                var bgClass = i % 2 === 0 ? 'bg-white dark:bg-gray-800' : 'bg-gray-50 dark:bg-gray-750';
                html += '<tr class="' + bgClass + '">';
                data.headers.forEach(function(h) {
                    html += '<td class="px-3 py-2 border-b border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-300">' + AttachmentGallery.escapeHtml(row[h] || '') + '</td>';
                });
                html += '</tr>';
            });
            
            html += '</tbody></table></div>';
            html += '<div class="mt-2 text-sm text-gray-500 dark:text-gray-400">' + data.rowCount + ' rows</div>';
            
            return html;
        },
        
        renderJsonView: function(data) {
            var formatted = data.formatted || JSON.stringify(data.data, null, 2);
            return '<pre class="bg-gray-900 text-gray-100 p-4 rounded-lg overflow-auto text-sm" style="max-height: 400px;"><code class="language-json text-gray-100">' + 
                   this.escapeHtml(formatted) + 
                   '</code></pre>';
        },
        
        initIframeToggles: function() {
            document.querySelectorAll('.iframe-toggle').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    var card = this.closest('.web-card');
                    var preview = card.querySelector('.iframe-preview');
                    var iframe = preview.querySelector('iframe');
                    var src = this.dataset.src;
                    
                    if (preview.classList.contains('hidden')) {
                        iframe.src = src;
                        preview.classList.remove('hidden');
                        this.textContent = 'Hide';
                    } else {
                        iframe.src = '';
                        preview.classList.add('hidden');
                        this.textContent = 'Preview';
                    }
                });
            });
        },
        
        openLightbox: function(index) {
            if (!this.lightbox || this.currentImages.length === 0) return;
            
            this.currentIndex = index;
            this.updateLightboxImage();
            this.lightbox.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        },
        
        closeLightbox: function() {
            if (!this.lightbox) return;
            
            this.lightbox.classList.add('hidden');
            document.body.style.overflow = '';
        },
        
        prevImage: function() {
            this.currentIndex = (this.currentIndex - 1 + this.currentImages.length) % this.currentImages.length;
            this.updateLightboxImage();
        },
        
        nextImage: function() {
            this.currentIndex = (this.currentIndex + 1) % this.currentImages.length;
            this.updateLightboxImage();
        },
        
        updateLightboxImage: function() {
            var img = this.currentImages[this.currentIndex];
            if (!img) return;
            
            var lightboxImg = this.lightbox.querySelector('.lightbox-content img');
            var caption = this.lightbox.querySelector('.lightbox-caption');
            
            lightboxImg.src = img.src;
            lightboxImg.alt = img.name;
            caption.textContent = img.name + ' (' + (this.currentIndex + 1) + ' / ' + this.currentImages.length + ')';
        },
        
        escapeHtml: function(text) {
            var div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    };
    
    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            AttachmentGallery.init();
        });
    } else {
        AttachmentGallery.init();
    }
    
})();
