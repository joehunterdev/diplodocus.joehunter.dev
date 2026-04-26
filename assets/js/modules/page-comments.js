/**
 * ===========================================
 * PageComments
 * ===========================================
 *
 * Allows users to add and view notes per page, stored in localStorage.
 *
 * Features:
 * - Add note via modal dialog (prompt)
 * - View all notes in a comments panel
 * - Instant delete (no confirmation)
 * - Timestamp each note
 * - Per-page storage in localStorage
 *
 * @usage HTML
 *   <div data-content-article>
 *       <!-- Comments button and panel injected here -->
 *   </div>
 */

const PageComments = (function () {
    'use strict';

    // -- Private: Config --
    const CONFIG = {
        debug: false,
        eventNamespace: '.pageComments',
        storageKey: 'dc-comments',
    };

    // -- Private: Selectors --
    const SELECTORS = {
        contentArea: '[data-content-article]',
        addCommentBtn: '[data-add-comment]',
        deleteCommentBtn: '[data-delete-comment]',
        commentsPanel: '[data-comments-panel]',
    };

    // -- Private: State --
    let state = {
        initialized: false,
        comments: [],
    };

    // -- Private: Cached DOM --
    let $contentArea = null;
    let $commentsPanel = null;

    // -- Private: Utils --
    function log(...args) {
        if (CONFIG.debug) {
            console.log('[PageComments]', ...args);
        }
    }

    function setState(newState) {
        state = Object.assign({}, state, newState);
        log('State updated:', state);
    }

    /**
     * Get page ID from URL and window.projectData
     */
    function getPageId() {
        if (typeof window.projectData === 'object' && window.projectData.slug) {
            var pageSlug = window.location.pathname.split('/').pop() || 'index';
            return window.projectData.slug + ':' + pageSlug;
        }
        return null;
    }

    /**
     * Load comments from localStorage
     */
    function loadComments() {
        var pageId = getPageId();
        if (!pageId) return [];

        var stored = localStorage.getItem(CONFIG.storageKey);
        if (!stored) return [];

        try {
            var allComments = JSON.parse(stored);
            return allComments[pageId] || [];
        } catch (e) {
            log('Failed to parse comments:', e);
            return [];
        }
    }

    /**
     * Save comments to localStorage
     */
    function saveComments(comments) {
        var pageId = getPageId();
        if (!pageId) return;

        var stored = localStorage.getItem(CONFIG.storageKey);
        var allComments = {};

        try {
            if (stored) allComments = JSON.parse(stored);
        } catch (e) {
            log('Failed to parse comments, overwriting:', e);
        }

        allComments[pageId] = comments;
        try {
            localStorage.setItem(CONFIG.storageKey, JSON.stringify(allComments));
            log('Saved', comments.length, 'comment(s)');
        } catch (e) {
            log('Failed to save comments:', e);
        }
    }

    /**
     * Format timestamp as readable date
     */
    function formatDate(isoString) {
        try {
            var date = new Date(isoString);
            var dateStr = date.toLocaleDateString();
            var timeStr = date.toLocaleTimeString([], {
                hour: '2-digit',
                minute: '2-digit',
            });
            return dateStr + ' ' + timeStr;
        } catch (e) {
            return isoString;
        }
    }

    // -- Private: Setup --
    function cacheDom() {
        var $ = window.jQuery;
        $contentArea = $(SELECTORS.contentArea);
        $commentsPanel = $(SELECTORS.commentsPanel);
    }

    function setup() {
        cacheDom();
        if (!$contentArea.length) {
            log('Content area not found');
            return false;
        }
        return true;
    }

    /**
     * Create button and panel HTML
     */
    function createUI() {
        var $ = window.jQuery;
        var buttonHtml = '<div style="margin:1rem 0;">' +
            '<button data-add-comment style="padding:0.5rem 1rem; background:var(--dc-brand-primary-soft); color:var(--dc-brand-primary); border:1px solid var(--dc-border-default); border-radius:var(--dc-radius-md); cursor:pointer; font-size:0.9rem;">💬 Add note</button>' +
            '</div>';

        var panelHtml = '<div data-comments-panel style="margin:1.5rem 0; padding:0; background:var(--dc-bg-surface); border:1px solid var(--dc-border-subtle); border-radius:var(--dc-radius-lg); overflow:hidden;"></div>';

        $contentArea.before(buttonHtml);
        $contentArea.after(panelHtml);

        cacheDom();
    }

    /**
     * Render comments panel
     */
    function renderPanel() {
        var $ = window.jQuery;
        var comments = loadComments();

        log('Rendering', comments.length, 'comment(s)');

        if (comments.length === 0) {
            $commentsPanel.html(
                '<div style="padding:1rem; text-align:center; color:var(--dc-text-muted);">No notes yet</div>'
            );
            return;
        }

        var html = '<div style="padding:0.5rem;">';
        comments.forEach(function (comment) {
            html +=
                '<div data-comment-id="' +
                comment.id +
                '" style="padding:0.75rem; border:1px solid var(--dc-border-subtle); border-radius:var(--dc-radius-sm); margin-bottom:0.5rem; background:var(--dc-bg-elevated);">' +
                '<div style="display:flex; justify-content:space-between; align-items:start; margin-bottom:0.5rem;">' +
                '<small style="color:var(--dc-text-muted);">' +
                formatDate(comment.timestamp) +
                '</small>' +
                '<button data-delete-comment data-comment-id="' +
                comment.id +
                '" style="background:none; border:none; color:var(--dc-text-muted); cursor:pointer; padding:0; font-size:1rem;" title="Delete this note">✕</button>' +
                '</div>' +
                '<p style="margin:0; color:var(--dc-text-primary);">' +
                comment.text.replace(/</g, '&lt;').replace(/>/g, '&gt;') +
                '</p>' +
                '</div>';
        });
        html += '</div>';

        $commentsPanel.html(html);
    }

    // -- Private: Events --
    function bindEvents() {
        var $ = window.jQuery;

        // Add comment button
        $(document).on('click' + CONFIG.eventNamespace, SELECTORS.addCommentBtn, function (e) {
            e.preventDefault();
            var text = prompt('Add a note about this page:');
            if (text) {
                addComment(text);
            }
        });

        // Delete comment button
        $(document).on('click' + CONFIG.eventNamespace, SELECTORS.deleteCommentBtn, function (e) {
            e.preventDefault();
            var commentId = parseInt($(this).data('comment-id'));
            deleteComment(commentId);
        });
    }

    function unbindEvents() {
        window.jQuery(document).off(CONFIG.eventNamespace);
    }

    // -- Private: Comment Actions --
    function addComment(text) {
        if (!text || !text.trim()) return;

        var comments = loadComments();
        comments.push({
            id: Date.now(),
            text: text.trim(),
            timestamp: new Date().toISOString(),
        });

        saveComments(comments);
        setState({ comments: comments });
        renderPanel();
        log('Added comment:', text);
    }

    function deleteComment(commentId) {
        var comments = loadComments().filter(function (c) {
            return c.id !== commentId;
        });

        saveComments(comments);
        setState({ comments: comments });
        renderPanel();
        log('Deleted comment:', commentId);
    }

    // -- Public: Init --
    function init() {
        var $ = window.jQuery;

        if (!$) {
            console.warn('[PageComments] jQuery not available');
            return;
        }

        if (!setup()) return;
        if (state.initialized) return;

        createUI();
        bindEvents();
        setState({ initialized: true, comments: loadComments() });
        renderPanel();

        log('Initialized');
    }

    // -- Public: Destroy --
    function destroy() {
        if (!state.initialized) return;

        unbindEvents();
        setState({ initialized: false, comments: [] });

        log('Destroyed');
    }

    // -- Public API --
    return { init: init, destroy: destroy };
})();

window.PageComments = PageComments;
