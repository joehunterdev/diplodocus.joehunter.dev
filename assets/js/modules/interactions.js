/**
 * ===========================================
 * Annotation
 * ===========================================
 *
 * Inline document annotations stored in localStorage.
 * Select text in the content area to add a comment or tag an attachment.
 * Annotations appear as right-margin markers; click to open popover.
 *
 * @usage HTML
 *   <article data-content-article class="prose">...</article>
 *
 * Requires:
 *   window.projectData  â€” { slug, attachments[] }
 *   window.attachmentBase â€” e.g. '/getting-started/attachments/'
 */

const Interactions = (function () {
    'use strict';

    // -- Private: Config --
    const CONFIG = {
        debug: false,
        eventNamespace: '.interactions',
        storageKey: 'dc-interactions',
        debounceMs: 150,
        imageExts: ['png', 'jpg', 'jpeg', 'gif', 'webp', 'svg'],
    };

    // -- Private: Selectors --
    const SELECTORS = {
        article:        '[data-content-article]',
        triggerBtn:     '[data-ix-trigger-btn]',
        modal:          '[data-ix-modal]',
        modalTab:       '[data-ix-tab]',
        tabPanel:       '[data-ix-tab-panel]',
        commentArea:    '[data-ix-comment]',
        attachmentGrid: '[data-ix-attachment-grid]',
        attachmentItem: '[data-ix-attachment-item]',
        saveBtn:        '[data-ix-save]',
        cancelBtn:      '[data-ix-cancel]',
        marker:         '[data-ix-marker]',
        markerTrigger:  '[data-ix-trigger]',
        popover:        '[data-ix-popover]',
        deleteBtn:      '[data-ix-delete]',
        mobileToggle:   '[data-ix-mobile-toggle]',
    };

    const BLOCK_SELECTOR = 'p, h1, h2, h3, h4, h5, h6, li, blockquote, pre, table';

    // -- Private: State --
    let state = {
        initialized: false,
        annotations: [],
        pendingAnchor: null,
        selectedAttachment: null,
        activeTab: 'comment',
        activePopover: null,
        mobileMarkersVisible: false,
    };

    // -- Private: Cached DOM --
    let $article = null;
    let $triggerBtn = null;
    let $modal = null;
    let $mobileToggle = null;
    let resizeTimer = null;

    // -- Private: Utils --
    function log(...args) {
        if (CONFIG.debug) console.log('[Interactions]', ...args);
    }

    function setState(newState) {
        state = Object.assign({}, state, newState);
    }

    function escapeHtml(str) {
        var div = document.createElement('div');
        div.textContent = String(str || '');
        return div.innerHTML;
    }

    function formatDate(iso) {
        try {
            return new Date(iso).toLocaleDateString(undefined, { day: 'numeric', month: 'short', year: 'numeric' });
        } catch (e) { return iso; }
    }

    function isImageFile(filename) {
        var ext = (filename || '').split('.').pop().toLowerCase();
        return CONFIG.imageExts.indexOf(ext) !== -1;
    }

    // -- Private: Storage --
    function getPageKey() {
        if (!window.projectData || !window.projectData.slug) return null;
        var pageSlug = window.location.pathname.split('/').filter(Boolean).pop() || 'index';
        return window.projectData.slug + ':' + pageSlug;
    }

    function loadAnnotations() {
        var key = getPageKey();
        if (!key) return [];
        try {
            var stored = JSON.parse(localStorage.getItem(CONFIG.storageKey) || '{}');
            return stored[key] || [];
        } catch (e) { return []; }
    }

    function saveAnnotations(annotations) {
        var key = getPageKey();
        if (!key) return;
        try {
            var stored = JSON.parse(localStorage.getItem(CONFIG.storageKey) || '{}');
            stored[key] = annotations;
            localStorage.setItem(CONFIG.storageKey, JSON.stringify(stored));
        } catch (e) { log('Storage write failed:', e); }
    }

    // -- Private: Block indexing --
    function indexBlocks() {
        var idx = 0;
        $article.find(BLOCK_SELECTOR).each(function () {
            window.jQuery(this).attr('data-block-index', idx++);
        });
        log('Indexed', idx, 'blocks');
    }

    function getSelectionBlockIndex() {
        var sel = window.getSelection();
        if (!sel || sel.rangeCount === 0 || sel.isCollapsed) return null;
        var node = sel.getRangeAt(0).startContainer;
        if (node.nodeType === 3) node = node.parentElement;
        while (node && node !== $article.get(0)) {
            if (node.hasAttribute && node.hasAttribute('data-block-index')) {
                return parseInt(node.getAttribute('data-block-index'), 10);
            }
            node = node.parentElement;
        }
        return null;
    }

    // -- Private: Marker positioning --
    function getBlockTop(blockIndex) {
        var $block = $article.find('[data-block-index="' + blockIndex + '"]');
        if (!$block.length) return 0;
        var articleRect = $article.get(0).getBoundingClientRect();
        var blockRect = $block.get(0).getBoundingClientRect();
        var scrollParent = document.querySelector('[data-main-scroll]');
        var scrollTop = scrollParent ? scrollParent.scrollTop : window.pageYOffset;
        return blockRect.top - articleRect.top + scrollTop;
    }

    // -- Private: DOM injection --
    function injectTriggerBtn() {
        $triggerBtn = window.jQuery(
            '<button data-ix-trigger-btn class="dc-ix-trigger-btn" hidden>+ Annotate</button>'
        );
        window.jQuery('body').append($triggerBtn);
    }

    function injectModal() {
        var html =
            '<div data-ix-modal class="dc-ix-modal" hidden>' +
              '<div class="dc-ix-modal-panel">' +
                '<div class="dc-ix-tabs">' +
                  '<button data-ix-tab="comment" class="dc-ix-tab is-active">Comment</button>' +
                  '<button data-ix-tab="attachment" class="dc-ix-tab">Attachment</button>' +
                '</div>' +
                '<div data-ix-tab-panel="comment">' +
                  '<textarea data-ix-comment class="dc-ix-textarea" placeholder="Add a comment..."></textarea>' +
                '</div>' +
                '<div data-ix-tab-panel="attachment" hidden>' +
                  '<div data-ix-attachment-grid class="dc-ix-attachment-grid"></div>' +
                '</div>' +
                '<div class="dc-ix-modal-actions">' +
                  '<button data-ix-cancel class="dc-ix-btn-cancel">Cancel</button>' +
                  '<button data-ix-save class="dc-ix-btn-save">Save</button>' +
                '</div>' +
              '</div>' +
            '</div>';
        window.jQuery('body').append(html);
        $modal = window.jQuery(SELECTORS.modal);
    }

    function injectMobileToggle() {
        $mobileToggle = window.jQuery(
            '<button data-ix-mobile-toggle class="dc-ix-mobile-toggle">&#x1F4AC; Annotations</button>'
        );
        $article.prepend($mobileToggle);
    }

    // -- Private: Attachment picker --
    function renderAttachmentPicker() {
        var $ = window.jQuery;
        var attachments = (window.projectData && window.projectData.attachments) || [];
        var base = window.attachmentBase || '';
        var $grid = $(SELECTORS.attachmentGrid);
        $grid.empty();

        if (!attachments.length) {
            $grid.html('<p style="color:var(--dc-text-muted);font-size:.8rem;padding:.5rem 0">No attachments in this project.</p>');
            return;
        }

        attachments.forEach(function (filename) {
            var url = base + encodeURIComponent(filename);
            var inner = isImageFile(filename)
                ? '<img src="' + url + '" alt="' + escapeHtml(filename) + '"><span>' + escapeHtml(filename) + '</span>'
                : '<div class="dc-ix-attachment-item-icon">&#x1F4CE;</div><span>' + escapeHtml(filename) + '</span>';
            $grid.append(
                '<button class="dc-ix-attachment-item" data-ix-attachment-item="' + escapeHtml(filename) + '">' +
                inner + '</button>'
            );
        });
    }

    // -- Private: Modal --
    function openModal(blockIndex, selectedText) {
        var $ = window.jQuery;

        // Find nearest heading at or before this block
        var headingId = null;
        $article.find('h1[id],h2[id],h3[id],h4[id],h5[id],h6[id]').each(function () {
            if (parseInt($(this).attr('data-block-index'), 10) <= blockIndex) {
                headingId = this.id;
            }
        });

        setState({
            pendingAnchor: { blockIndex: blockIndex, headingId: headingId, selectedText: selectedText },
            selectedAttachment: null,
            activeTab: 'comment',
        });

        $(SELECTORS.commentArea).val('');
        $(SELECTORS.attachmentItem).removeClass('is-selected');
        switchTab('comment');
        $modal.removeAttr('hidden');
        var textarea = $(SELECTORS.commentArea).get(0);
        if (textarea) textarea.focus();
        hideTriggerBtn();
    }

    function closeModal() {
        $modal.attr('hidden', '');
        setState({ pendingAnchor: null, selectedAttachment: null });
    }

    function switchTab(tab) {
        var $ = window.jQuery;
        $(SELECTORS.modalTab).removeClass('is-active');
        $('[data-ix-tab="' + tab + '"]').addClass('is-active');
        $('[data-ix-tab-panel]').each(function () {
            var $p = $(this);
            if ($p.attr('data-ix-tab-panel') === tab) {
                $p.removeAttr('hidden');
            } else {
                $p.attr('hidden', '');
            }
        });
        if (tab === 'attachment') renderAttachmentPicker();
        setState({ activeTab: tab });
    }

    function hideTriggerBtn() {
        $triggerBtn.attr('hidden', '');
    }

    // -- Private: Save --
    function handleSave() {
        var $ = window.jQuery;
        if (!state.pendingAnchor) return;

        var annotation;

        if (state.activeTab === 'comment') {
            var text = $(SELECTORS.commentArea).val().trim();
            if (!text) return;
            annotation = {
                id: Date.now(),
                type: 'comment',
                text: text,
                anchorBlockIndex: state.pendingAnchor.blockIndex,
                anchorHeadingId: state.pendingAnchor.headingId,
                anchorSelectedText: state.pendingAnchor.selectedText,
                created: new Date().toISOString(),
            };
        } else {
            if (!state.selectedAttachment) return;
            annotation = {
                id: Date.now(),
                type: 'attachment',
                attachmentFilename: state.selectedAttachment,
                anchorBlockIndex: state.pendingAnchor.blockIndex,
                anchorHeadingId: state.pendingAnchor.headingId,
                anchorSelectedText: state.pendingAnchor.selectedText,
                created: new Date().toISOString(),
            };
        }

        var annotations = state.annotations.concat([Interactions]);
        setState({ annotations: annotations, pendingAnchor: null, selectedAttachment: null });
        saveAnnotations(annotations);
        closeModal();
        renderMarker(annotation);
        window.getSelection().removeAllRanges();
        log('Saved:', annotation.id);
    }

    // -- Private: Markers --
    function renderMarker(annotation) {
        var top = getBlockTop(annotation.anchorBlockIndex);
        var isAttachment = annotation.type === 'attachment';
        var icon = isAttachment ? '&#x1F4CE;' : '&#x1F4AC;';
        var typeClass = 'dc-ix-marker--' + annotation.type;
        var id = annotation.id;

        var popoverBody;
        if (isAttachment) {
            var url = (window.attachmentBase || '') + encodeURIComponent(annotation.attachmentFilename);
            popoverBody = isImageFile(annotation.attachmentFilename)
                ? '<img src="' + url + '" class="dc-ix-thumb" alt="' + escapeHtml(annotation.attachmentFilename) + '">'
                : '<div class="dc-ix-attachment-item-icon">&#x1F4CE;</div>';
            popoverBody += '<span class="dc-ix-filename">' + escapeHtml(annotation.attachmentFilename) + '</span>';
        } else {
            popoverBody =
                '<p class="dc-ix-popover-text">' + escapeHtml(annotation.text) + '</p>' +
                '<time class="dc-ix-popover-time">' + formatDate(annotation.created) + '</time>';
        }

        $article.append(
            '<div data-ix-marker data-ix-id="' + id + '"' +
            ' class="dc-ix-marker ' + typeClass + '" style="top:' + top + 'px;">' +
              '<button data-ix-trigger="' + id + '" class="dc-ix-icon-btn" title="View annotation">' + icon + '</button>' +
              '<div data-ix-popover="' + id + '" class="dc-ix-popover" hidden>' +
                '<div class="dc-ix-popover-body">' + popoverBody + '</div>' +
                '<div class="dc-ix-popover-footer">' +
                  '<button data-ix-delete="' + id + '" class="dc-ix-delete-btn">Delete</button>' +
                '</div>' +
              '</div>' +
            '</div>'
        );
    }

    function renderAllMarkers() {
        state.annotations.forEach(renderMarker);
    }

    function recalcPositions() {
        state.annotations.forEach(function (annotation) {
            window.jQuery('[data-ix-marker][data-ix-id="' + annotation.id + '"]')
                .css('top', getBlockTop(annotation.anchorBlockIndex) + 'px');
        });
    }

    function onResize() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(recalcPositions, CONFIG.debounceMs);
    }

    // -- Private: Popovers --
    function openPopover(id) {
        closeAllPopovers();
        window.jQuery('[data-ix-popover="' + id + '"]').removeAttr('hidden');
        setState({ activePopover: id });
    }

    function closeAllPopovers() {
        window.jQuery(SELECTORS.popover).attr('hidden', '');
        setState({ activePopover: null });
    }

    // -- Private: Delete --
    function deleteAnnotation(id) {
        var numId = parseInt(id, 10);
        var annotations = state.annotations.filter(function (a) { return a.id !== numId; });
        setState({ annotations: annotations });
        saveAnnotations(annotations);
        window.jQuery('[data-ix-marker][data-ix-id="' + id + '"]').remove();
        log('Deleted:', id);
    }

    // -- Private: Events --
    function bindEvents() {
        var $ = window.jQuery;
        var ns = CONFIG.eventNamespace;

        // Show trigger button on text selection within article
        $article.on('mouseup' + ns, function () {
            var sel = window.getSelection();
            if (!sel || sel.isCollapsed || !sel.toString().trim()) {
                hideTriggerBtn();
                return;
            }
            var range = sel.getRangeAt(0);
            if (!$article.get(0).contains(range.commonAncestorContainer)) {
                hideTriggerBtn();
                return;
            }
            var rect = range.getBoundingClientRect();
            $triggerBtn.css({
                top:  (rect.bottom + 6) + 'px',
                left: (rect.right - 60) + 'px',
            }).removeAttr('hidden');
        });

        // Open modal from trigger button
        $(document).on('click' + ns, SELECTORS.triggerBtn, function (e) {
            e.preventDefault();
            var sel = window.getSelection();
            var selectedText = sel ? sel.toString().trim().substring(0, 80) : '';
            var blockIndex = getSelectionBlockIndex();
            if (blockIndex === null) { hideTriggerBtn(); return; }
            openModal(blockIndex, selectedText);
        });

        // Hide trigger on mousedown outside it
        $(document).on('mousedown' + ns, function (e) {
            if (!$(e.target).closest(SELECTORS.triggerBtn).length) {
                hideTriggerBtn();
            }
        });

        // Modal tab switching
        $(document).on('click' + ns, SELECTORS.modalTab, function () {
            switchTab($(this).attr('data-ix-tab'));
        });

        // Attachment item selection
        $(document).on('click' + ns, SELECTORS.attachmentItem, function () {
            $(SELECTORS.attachmentItem).removeClass('is-selected');
            $(this).addClass('is-selected');
            setState({ selectedAttachment: $(this).attr('data-ix-attachment-item') });
        });

        // Save / cancel
        $(document).on('click' + ns, SELECTORS.saveBtn, handleSave);
        $(document).on('click' + ns, SELECTORS.cancelBtn, closeModal);

        // Close modal on overlay click
        $(document).on('click' + ns, SELECTORS.modal, function (e) {
            if ($(e.target).is(SELECTORS.modal)) closeModal();
        });

        // Marker popover toggle
        $(document).on('click' + ns, SELECTORS.markerTrigger, function (e) {
            e.stopPropagation();
            var id = $(this).attr('data-ix-trigger');
            if (state.activePopover === String(id)) {
                closeAllPopovers();
            } else {
                openPopover(id);
            }
        });

        // Close popovers on outside click
        $(document).on('click' + ns, function (e) {
            if (state.activePopover && !$(e.target).closest(SELECTORS.marker).length) {
                closeAllPopovers();
            }
        });

        // Delete
        $(document).on('click' + ns, SELECTORS.deleteBtn, function (e) {
            e.stopPropagation();
            deleteAnnotation($(this).attr('data-ix-delete'));
        });

        // Escape â€” close modal or popovers
        $(document).on('keydown' + ns, function (e) {
            if (e.key !== 'Escape') return;
            if (!$modal.attr('hidden')) { closeModal(); return; }
            if (state.activePopover) closeAllPopovers();
        });

        // Mobile markers toggle
        $(document).on('click' + ns, SELECTORS.mobileToggle, function () {
            var visible = !state.mobileMarkersVisible;
            setState({ mobileMarkersVisible: visible });
            $(SELECTORS.marker).toggleClass('is-visible', visible);
            $mobileToggle.html(visible ? '&#x1F4AC; Hide Annotations' : '&#x1F4AC; Annotations');
        });

        window.addEventListener('resize', onResize);
    }

    function unbindEvents() {
        $article.off(CONFIG.eventNamespace);
        window.jQuery(document).off(CONFIG.eventNamespace);
        window.removeEventListener('resize', onResize);
    }

    // -- Public: Init --
    function init() {
        var $ = window.jQuery;
        if (!$) { console.warn('[Interactions] jQuery not available'); return; }

        $article = $(SELECTORS.article);
        if (!$article.length) return;
        if (!window.projectData) return;
        if (state.initialized) return;

        indexBlocks();
        injectTriggerBtn();
        injectModal();
        injectMobileToggle();

        var annotations = loadAnnotations();
        setState({ initialized: true, annotations: annotations });
        renderAllMarkers();
        bindEvents();
        log('Initialized with', annotations.length, 'annotation(s)');
    }

    // -- Public: Destroy --
    function destroy() {
        if (!state.initialized) return;
        unbindEvents();
        if ($triggerBtn) $triggerBtn.remove();
        if ($modal) $modal.remove();
        if ($mobileToggle) $mobileToggle.remove();
        window.jQuery(SELECTORS.marker).remove();
        window.jQuery('[data-block-index]').removeAttr('data-block-index');
        setState({ initialized: false, annotations: [], activePopover: null });
        log('Destroyed');
    }

    // -- Public API --
    return { init: init, destroy: destroy };

})();

export default Interactions;

