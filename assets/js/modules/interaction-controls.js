/**
 * ===========================================
 * InteractionControls
 * ===========================================
 *
 * Sidebar widget showing a summary of interactions for this project:
 *
 *   ┌─────────────────────────────────┐
 *   │  [9] Interactions          ▾   │  ← toggle row, hidden when 0
 *   ├─────────────────────────────────┤
 *   │  3 Comments                     │  ← breakdown (hidden until toggled)
 *   │  5 Attachments                  │
 *   │  1 Checked                      │
 *   │  [Export]  [Import]  [Clear]    │
 *   └─────────────────────────────────┘
 *
 * Reads/writes the same localStorage key as Interactions ('dc-interactions').
 * Scoped to the current project slug (window.projectData.slug).
 */

const InteractionControls = (function () {
    'use strict';

    const STORAGE_KEY = 'dc-interactions';

    // DOM refs — set in init()
    var $controls = null;   // [data-ix-controls]
    var $toggle = null;   // [data-ix-controls-toggle]
    var $panel = null;   // [data-ix-controls-panel]
    var $count = null;   // [data-ix-count]
    var $breakdown = null;   // [data-ix-breakdown]
    var $exportBtn = null;   // [data-ix-export]
    var $importInput = null;   // [data-ix-import]
    var $clearBtn = null;   // [data-ix-clear]

    // -- Helpers --

    function slug() {
        return window.projectData && window.projectData.slug
            ? window.projectData.slug : null;
    }

    function readStore() {
        try { return JSON.parse(localStorage.getItem(STORAGE_KEY) || '{}'); }
        catch (e) { return {}; }
    }

    function writeStore(data) {
        localStorage.setItem(STORAGE_KEY, JSON.stringify(data));
    }

    function projectEntries(store, s) {
        var result = {};
        Object.keys(store).forEach(function (k) {
            if (k === s || k.indexOf(s + ':') === 0) result[k] = store[k];
        });
        return result;
    }

    function computeCounts(entries) {
        var total = 0, attachments = 0, comments = 0, checked = 0;
        Object.keys(entries).forEach(function (k) {
            var arr = entries[k];
            if (!Array.isArray(arr)) return;
            arr.forEach(function (ix) {
                total++;
                if (ix.type === 'attachment') attachments++;
                else if (ix.type === 'checked') checked++;
                else comments++;
            });
        });
        return { total: total, attachments: attachments, comments: comments, checked: checked };
    }

    // -- Refresh UI --

    function refresh() {
        if (!$controls) return;
        var s = slug();
        if (!s) return;

        var entries = projectEntries(readStore(), s);
        var counts = computeCounts(entries);
        var isEmpty = counts.total === 0;

        // Always visible on project pages so Import is always accessible
        $controls.removeAttribute('hidden');

        // Badge — hidden when nothing stored yet
        if ($count) {
            $count.textContent = counts.total;
            $count.style.display = isEmpty ? 'none' : '';
        }

        // Breakdown rows
        if ($breakdown) {
            if (isEmpty) {
                $breakdown.textContent = 'No interactions yet.';
            } else {
                var rows = [];
                if (counts.comments) rows.push(counts.comments + ' Comment' + (counts.comments !== 1 ? 's' : ''));
                if (counts.attachments) rows.push(counts.attachments + ' Attachment' + (counts.attachments !== 1 ? 's' : ''));
                if (counts.checked) rows.push(counts.checked + ' Checked');
                $breakdown.textContent = rows.join(' · ');
            }
        }

        // Hide export/clear when nothing to act on
        if ($exportBtn) $exportBtn.style.display = isEmpty ? 'none' : '';
        if ($clearBtn) $clearBtn.style.display = isEmpty ? 'none' : '';
    }

    // -- Toggle panel --

    function togglePanel() {
        if (!$panel) return;
        if ($panel.hasAttribute('hidden')) {
            $panel.removeAttribute('hidden');
            $toggle && $toggle.classList.add('is-open');
        } else {
            $panel.setAttribute('hidden', '');
            $toggle && $toggle.classList.remove('is-open');
        }
    }

    // -- Export --

    function handleExport() {
        var s = slug();
        if (!s) return;
        var entries = projectEntries(readStore(), s);
        if (!Object.keys(entries).length) { alert('No interactions to export.'); return; }

        var payload = { project: s, exported: new Date().toISOString(), data: entries };
        var blob = new Blob([JSON.stringify(payload, null, 2)], { type: 'application/json' });
        var url = URL.createObjectURL(blob);
        var a = document.createElement('a');
        a.href = url;
        a.download = s + '-interactions-' + Date.now() + '.json';
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
    }

    // -- Import --

    function handleImport(file) {
        if (!file) return;
        var reader = new FileReader();
        reader.onload = function (e) {
            try {
                var payload = JSON.parse(e.target.result);
                if (!payload.data || typeof payload.data !== 'object') {
                    throw new Error('Invalid file — missing .data');
                }
                var store = readStore();
                var count = 0;
                Object.keys(payload.data).forEach(function (k) {
                    if (Array.isArray(payload.data[k])) {
                        store[k] = payload.data[k];
                        count += payload.data[k].length;
                    }
                });
                writeStore(store);
                refresh();
                alert('Imported ' + count + ' interaction(s). Reload the page to see markers.');
            } catch (err) {
                alert('Import failed: ' + err.message);
            }
        };
        reader.readAsText(file);
    }

    // -- Clear --

    function handleClear() {
        var s = slug();
        if (!s) return;
        var store = readStore();
        var entries = projectEntries(store, s);
        var keys = Object.keys(entries);
        if (!keys.length) { alert('Nothing to clear.'); return; }

        var counts = computeCounts(entries);
        if (!confirm('Delete all ' + counts.total + ' interaction(s) for this project?')) return;

        keys.forEach(function (k) { delete store[k]; });
        writeStore(store);
        refresh();
        window.location.reload();
    }

    // -- Init --

    function init() {
        $controls = document.querySelector('[data-ix-controls]');
        if (!$controls) return;
        if (!slug()) return;

        $toggle = $controls.querySelector('[data-ix-controls-toggle]');
        $panel = $controls.querySelector('[data-ix-controls-panel]');
        $count = $controls.querySelector('[data-ix-count]');
        $breakdown = $controls.querySelector('[data-ix-breakdown]');
        $exportBtn = $controls.querySelector('[data-ix-export]');
        $importInput = $controls.querySelector('[data-ix-import]');
        $clearBtn = $controls.querySelector('[data-ix-clear]');

        if ($toggle) $toggle.addEventListener('click', togglePanel);
        if ($exportBtn) $exportBtn.addEventListener('click', handleExport);
        if ($importInput) {
            $importInput.addEventListener('change', function () {
                handleImport(this.files[0]);
                this.value = '';
            });
        }
        if ($clearBtn) $clearBtn.addEventListener('click', handleClear);

        // React to saves from the Interactions module (same tab)
        window.addEventListener('dc:interactions:saved', refresh);

        // React to changes from other tabs
        window.addEventListener('storage', function (e) {
            if (e.key === STORAGE_KEY) refresh();
        });

        refresh();
    }

    function destroy() {
        // noop — sidebar is server-rendered, page navigation reloads it
    }

    return { init: init, destroy: destroy, refresh: refresh };

})();

export default InteractionControls;
