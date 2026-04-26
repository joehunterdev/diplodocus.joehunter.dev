/**
 * ===========================================
 * Interaction
 * ===========================================
 *
 * Inline document interactions stored in localStorage.
 * Select text in the content area to add a comment or tag an attachment.
 * interactions appear as right-margin markers; click to open popover.
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
        debug: true,
        eventNamespace: '.interactions',
        storageKey: 'dc-interactions',
        debounceMs: 150,
        imageExts: ['png', 'jpg', 'jpeg', 'gif', 'webp', 'svg'],
    };

    // -- Private: Selectors --
    const SELECTORS = {
        article: '[data-content-article]',
        triggerBtn: '[data-ix-trigger-btn]',
        modal: '[data-ix-modal]',
        modalTab: '[data-ix-tab]',
        tabPanel: '[data-ix-tab-panel]',
        commentArea: '[data-ix-comment]',
        attachmentGrid: '[data-ix-attachment-grid]',
        attachmentItem: '[data-ix-attachment-item]',
        saveBtn: '[data-ix-save]',
        cancelBtn: '[data-ix-cancel]',
        marker: '[data-ix-marker]',
        markerTrigger: '[data-ix-trigger]',
        popover: '[data-ix-popover]',
        deleteBtn: '[data-ix-delete]',
        mobileToggle: '[data-ix-mobile-toggle]',
    };

    const BLOCK_SELECTOR = 'p, h1, h2, h3, h4, h5, h6, li, blockquote, pre, table';

    // -- Private: State --
    let state = {
        initialized: false,
        interactions: [],
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
    let pendingBlockIndex = null;

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

    function loadInteractions() {
        var key = getPageKey();
        if (!key) return [];
        try {
            var stored = JSON.parse(localStorage.getItem(CONFIG.storageKey) || '{}');
            return stored[key] || [];
        } catch (e) { return []; }
    }

    function saveInteractions(interactions) {
        var key = getPageKey();
        if (!key) return;
        try {
            var stored = JSON.parse(localStorage.getItem(CONFIG.storageKey) || '{}');
            stored[key] = interactions;
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
            '<button data-ix-trigger-btn class="dc-ix-trigger-btn" hidden>+ Add note</button>'
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
            '<button data-ix-mobile-toggle class="dc-ix-mobile-toggle">&#x1F4AC; interactions</button>'
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

        var interaction;

        if (state.activeTab === 'comment') {
            var text = $(SELECTORS.commentArea).val().trim();
            if (!text) return;
            interaction = {
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
            interaction = {
                id: Date.now(),
                type: 'attachment',
                attachmentFilename: state.selectedAttachment,
                anchorBlockIndex: state.pendingAnchor.blockIndex,
                anchorHeadingId: state.pendingAnchor.headingId,
                anchorSelectedText: state.pendingAnchor.selectedText,
                created: new Date().toISOString(),
            };
        }

        var interactions = state.interactions.concat([interaction]);
        setState({ interactions: interactions, pendingAnchor: null, selectedAttachment: null });
        saveInteractions(interactions);
        closeModal();
        renderMarker(interaction);
        window.getSelection().removeAllRanges();
        log('Saved:', interaction.id);
    }

    // -- Private: Markers --
    function renderMarker(interaction) {
        var top = getBlockTop(interaction.anchorBlockIndex);
        var isAttachment = interaction.type === 'attachment';
        var icon = isAttachment ? '&#x1F4CE;' : '&#x1F4AC;';
        var typeClass = 'dc-ix-marker--' + interaction.type;
        var id = interaction.id;

        var popoverBody;
        if (isAttachment) {
            var url = (window.attachmentBase || '') + encodeURIComponent(interaction.attachmentFilename);
            popoverBody = isImageFile(interaction.attachmentFilename)
                ? '<img src="' + url + '" class="dc-ix-thumb" alt="' + escapeHtml(interaction.attachmentFilename) + '">'
                : '<div class="dc-ix-attachment-item-icon">&#x1F4CE;</div>';
            popoverBody += '<span class="dc-ix-filename">' + escapeHtml(interaction.attachmentFilename) + '</span>';
        } else {
            popoverBody =
                '<p class="dc-ix-popover-text">' + escapeHtml(interaction.text) + '</p>' +
                '<time class="dc-ix-popover-time">' + formatDate(interaction.created) + '</time>';
        }

        $article.append(
            '<div data-ix-marker data-ix-id="' + id + '"' +
            ' class="dc-ix-marker ' + typeClass + '" style="top:' + top + 'px;">' +
            '<button data-ix-trigger="' + id + '" class="dc-ix-icon-btn" title="View interaction">' + icon + '</button>' +
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
        state.interactions.forEach(renderMarker);
    }

    function recalcPositions() {
        state.interactions.forEach(function (interaction) {
            window.jQuery('[data-ix-marker][data-ix-id="' + interaction.id + '"]')
                .css('top', getBlockTop(interaction.anchorBlockIndex) + 'px');
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
    function deleteInteraction(id) {
        var numId = parseInt(id, 10);
        var interactions = state.interactions.filter(function (a) { return a.id !== numId; });
        setState({ interactions: interactions });
        saveInteractions(interactions);
        window.jQuery('[data-ix-marker][data-ix-id="' + id + '"]').remove();
        log('Deleted:', id);
    }

    // -- Private: Events --
    function bindEvents() {
        var $ = window.jQuery;
        var ns = CONFIG.eventNamespace;

        // Click any block element to show the trigger button — no text selection needed
        $article.on('click' + ns, BLOCK_SELECTOR, function (e) {
            // Ignore clicks on existing markers / popovers
            if ($(e.target).closest(SELECTORS.marker).length) return;
            var idx = parseInt($(this).attr('data-block-index'), 10);
            if (isNaN(idx)) return;
            pendingBlockIndex = idx;
            // Position the button near the click point
            $triggerBtn.css({
                top: (e.clientY + 10) + 'px',
                left: (e.clientX - 40) + 'px',
            }).removeAttr('hidden');
            // Prevent the document handler below from immediately hiding it
            e._ixHandled = true;
        });

        // Direct click on trigger button — open modal
        $triggerBtn.on('click' + ns, function (e) {
            e.preventDefault();
            e._ixHandled = true;
            log('Trigger btn clicked, pendingBlockIndex:', pendingBlockIndex);
            if (pendingBlockIndex === null) { hideTriggerBtn(); return; }
            var sel = window.getSelection();
            var selectedText = (sel && !sel.isCollapsed) ? sel.toString().trim().substring(0, 80) : '';
            openModal(pendingBlockIndex, selectedText);
        });

        // Hide trigger button on any document click not from a block or the button itself
        $(document).on('click' + ns, function (e) {
            if (e._ixHandled) return;
            hideTriggerBtn();
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
            deleteInteraction($(this).attr('data-ix-delete'));
        });

        // Escape â€” close modal or popovers
        $(document).on('keydown' + ns, function (e) {
            if (e.key !== 'Escape') return;
            if (!$modal.attr('hidden')) { closeModal(); return; }
            if (state.activePopover) closeAllPopovers();
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

        var interactions = loadInteractions();
        setState({ initialized: true, interactions: interactions });
        renderAllMarkers();
        bindEvents();
        log('Initialized with', interactions.length, 'interaction(s)');
    }

    // -- Public: Destroy --
    function destroy() {
        if (!state.initialized) return;
        unbindEvents();
        if ($triggerBtn) $triggerBtn.remove();
        if ($modal) $modal.remove();
        window.jQuery(SELECTORS.marker).remove();
        window.jQuery('[data-block-index]').removeAttr('data-block-index');
        setState({ initialized: false, interactions: [], activePopover: null });
        log('Destroyed');
    }

    // -- Public API --
    return { init: init, destroy: destroy };

})();

export default Interactions;

