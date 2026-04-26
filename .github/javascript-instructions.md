# 📦 JavaScript Module Conventions (V3)

> Follow these rules exactly when creating or editing JavaScript modules.

---

# 🚨 CRITICAL RULES

1. **NEVER add inline logic to `assets/js/app.js`**
   → Always create/use a module in `assets/js/modules/`

2. **ALWAYS check if a module already exists before creating a new one**

3. **ALWAYS use the Revealing Module Pattern (IIFE)**

4. **Register the module by importing it in `app.js` and calling `.init()`**
   → `modules-init.js` and `PageLogic` are removed — `app.js` is the single init point

5. **NEVER use IDs or classes for JS hooks**
   → Use `data-*` attributes ONLY

6. **ALWAYS use jQuery (`window.jQuery`)**

7. **ALWAYS check element existence before initializing**
   → Silent exit if not present

8. **PREFER scoped event delegation over `document`**

9. **ALL events MUST be namespaced**

10. **ALL modules MUST implement `destroy()`**

11. **ALWAYS cache DOM elements (no repeated queries)**

12. **STATE must ONLY be updated via `setState()`**

13. **ALL console logs MUST be prefixed `[ModuleName]`**

---

# 🧱 MODULE TEMPLATE (REQUIRED)

```js
/**
 * ===========================================
 * ModuleName
 * ===========================================
 *
 * Description...
 *
 * @usage HTML
 *   <div data-module-container>
 *       <button data-module-action="click">Click</button>
 *   </div>
 */

const ModuleName = (function () {

    // -- Private: Config --
    const CONFIG = {
        debug: false,
        eventNamespace: '.moduleName',
    };

    // -- Private: Selectors --
    const SELECTORS = {
        container: '[data-module-container]',
        trigger:   '[data-module-action]',
    };

    // -- Private: State --
    let state = {
        initialized: false,
    };

    // -- Private: Cached DOM --
    let $container = null;

    // -- Private: Utils --
    function log(...args) {
        if (CONFIG.debug) console.log('[ModuleName]', ...args);
    }

    function setState(newState) {
        state = Object.assign({}, state, newState);
        log('State updated:', state);
    }

    // -- Private: Setup --
    function cacheDom() {
        $container = window.jQuery(SELECTORS.container);
    }

    function setup() {
        cacheDom();
        return $container.length > 0;
    }

    // -- Private: Events --
    function bindEvents() {
        $container.on('click' + CONFIG.eventNamespace, SELECTORS.trigger, handleClick);
    }

    function unbindEvents() {
        if ($container) $container.off(CONFIG.eventNamespace);
    }

    // -- Private: Handlers --
    function handleClick(e) {
        e.preventDefault();
        const $el = window.jQuery(this);
        log('Clicked:', $el);
        setState({ lastAction: $el.data('module-action') });
    }

    // -- Public: Init --
    function init() {
        const $ = window.jQuery;
        if (!$) { console.warn('[ModuleName] jQuery not available'); return; }
        if (!setup()) return;
        if (state.initialized) return;
        bindEvents();
        setState({ initialized: true });
        log('Initialized');
    }

    // -- Public: Destroy --
    function destroy() {
        if (!state.initialized) return;
        unbindEvents();
        setState({ initialized: false });
        log('Destroyed');
    }

    // -- Public API --
    return { init, destroy };

})();

export default ModuleName;
```

---

# 🔌 MODULE REGISTRATION (REQUIRED)

Add every new module to `assets/js/app.js`:

```js
import MyModule from './modules/my-module.js';

// inside DOMContentLoaded:
MyModule.init();
```

That's it. No `modules-init.js`, no `PageLogic.register()` — `app.js` is the single source of truth.

---

# 🧷 DATA ATTRIBUTES (REQUIRED)

### ✅ Correct

```html
<div data-module-container>
    <button data-module-action="save">Save</button>
</div>
```

```js
const SELECTORS = {
    trigger: '[data-module-action]',
};
```

---

### ❌ Forbidden

```js
$('#myButton')
document.getElementById('myButton')
document.querySelector('.btn')
```

---

# ⚡ EVENT HANDLING RULES

### ✅ Scoped Delegation (Preferred)

```js
$container.on('click.moduleName', SELECTORS.trigger, handler);
```

### ⚠️ Fallback (ONLY if necessary)

```js
$(document).on('click.moduleName', SELECTORS.trigger, handler);
```

---

# 🧠 STATE MANAGEMENT

### ✅ Always use:

```js
setState({ key: value });
```

### ❌ Never:

```js
state.key = value;
```

---

# 🚀 AJAX PATTERN (STANDARD)

```js
let currentRequest = null;

function fetchData() {
    if (currentRequest) currentRequest.abort();

    currentRequest = window.jQuery.ajax({
        url: window.location.pathname,
        method: 'GET',
        data: state,
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        beforeSend: function () { $container.addClass('loading'); },
        success:    function (response) { $container.html(response.html); },
        error:      function (xhr) {
            if (xhr.statusText !== 'abort') console.error('[ModuleName] Request failed:', xhr);
        },
        complete:   function () { $container.removeClass('loading'); currentRequest = null; },
    });
}
```

---

# 🏷️ NAMING CONVENTIONS

| Item        | Format         | Example              |
| ----------- | -------------- | -------------------- |
| File        | kebab-case     | `search-filter.js`   |
| Module      | PascalCase     | `SearchFilter`       |
| Selectors   | UPPER_CASE obj | `SELECTORS.trigger`  |
| Functions   | camelCase      | `handleClick`        |
| jQuery vars | `$prefix`      | `$container`         |

---

# 🧩 CHECKLIST (AGENT MUST FOLLOW)

Before completing any task:

* [ ] Module does not already exist
* [ ] Uses IIFE pattern
* [ ] Ends with `export default ModuleName;`
* [ ] Imported and `.init()` called in `app.js`
* [ ] Uses ONLY `data-*` selectors
* [ ] Uses jQuery via `window.jQuery`
* [ ] Uses scoped event delegation
* [ ] Events are namespaced
* [ ] DOM is cached
* [ ] State updated via `setState()`
* [ ] `init()` includes silent exit if element missing
* [ ] `destroy()` implemented
* [ ] Console logs are prefixed `[ModuleName]`

---

# 🏁 SUMMARY

This system guarantees:

* Single entry point (`app.js`) — clear load order
* No event duplication bugs
* Safe dynamic DOM handling
* Scalable modular architecture
* Clean separation of concerns
* No build step required


---

# 🧱 MODULE TEMPLATE (REQUIRED)

```js
/**
 * ===========================================
 * ModuleName
 * ===========================================
 * 
 * Description...
 * 
 * @usage HTML
 *   <div data-module-container>
 *       <button data-module-action="click">Click</button>
 *   </div>
 */

const ModuleName = (function() {
    
    // -- Private: Config --
    const CONFIG = {
        debug: true,
        eventNamespace: '.moduleName',
    };
    
    // -- Private: Selectors --
    const SELECTORS = {
        container: '[data-module-container]',
        trigger: '[data-module-action]',
    };
    
    // -- Private: State --
    let state = {
        initialized: false,
    };
    
    // -- Private: Cached DOM --
    let $container = null;
    
    // -- Private: Utils --
    function log(...args) {
        if (CONFIG.debug) {
            console.log('[ModuleName]', ...args);
        }
    }
    
    function setState(newState) {
        state = { ...state, ...newState };
        log('State updated:', state);
    }
    
    // -- Private: Setup --
    function cacheDom() {
        $container = $(SELECTORS.container);
    }
    
    function setup() {
        cacheDom();
        if (!$container.length) return false;
        return true;
    }
    
    // -- Private: Events --
    function bindEvents() {
        $container.on(`click${CONFIG.eventNamespace}`, SELECTORS.trigger, handleClick);
    }
    
    function unbindEvents() {
        if ($container) {
            $container.off(CONFIG.eventNamespace);
        }
    }
    
    // -- Private: Handlers --
    function handleClick(e) {
        e.preventDefault();
        const $el = $(this);
        
        log('Clicked:', $el);
        
        setState({
            lastAction: $el.data('module-action'),
        });
    }
    
    // -- Public: Init --
    function init() {
        const $ = window.jQuery;
        
        if (!$) {
            console.warn('[ModuleName] jQuery not available');
            return;
        }
        
        if (!setup()) return;
        if (state.initialized) return;
        
        bindEvents();
        
        setState({ initialized: true });
        log('Initialized');
    }
    
    // -- Public: Destroy --
    function destroy() {
        if (!state.initialized) return;
        
        unbindEvents();
        setState({ initialized: false });
        
        log('Destroyed');
    }
    
    // -- Public API --
    return { init, destroy };
    
})();

export default ModuleName;
export const initModuleName = ModuleName.init;
```

---

# 🔌 MODULE REGISTRATION (REQUIRED)

Add every module to:

`resources/js/modules-init.js`

```js
import { registerModule } from './utils/page-logic';
import { initModuleName } from './modules/module-name';

registerModule('moduleName', initModuleName, {
    context: 'global',  // global | auth | admin | employee | employer
    priority: 80,       // 100=critical, 90=modals, 80=forms, 70=features
});
```

---

# 🧷 DATA ATTRIBUTES (REQUIRED)

### ✅ Correct

```html
<div data-module-container>
    <button data-module-action="save">Save</button>
</div>
```

```js
const SELECTORS = {
    trigger: '[data-module-action]',
};
```

---

### ❌ Forbidden

```js
$('#myButton')
document.getElementById('myButton')
document.querySelector('.btn')
```

---

# ⚡ EVENT HANDLING RULES

### ✅ Scoped Delegation (Preferred)

```js
$container.on('click.moduleName', SELECTORS.trigger, handler);
```

### ⚠️ Fallback (ONLY if necessary)

```js
$(document).on('click.moduleName', SELECTORS.trigger, handler);
```

---

# 🧠 STATE MANAGEMENT

### ✅ Always use:

```js
setState({ key: value });
```

### ❌ Never:

```js
state.key = value;
```

---

# 🚀 AJAX PATTERN (STANDARD)

```js
let currentRequest = null;

function fetchData() {
    if (currentRequest) currentRequest.abort();
    
    currentRequest = $.ajax({
        url: window.location.pathname,
        method: 'GET',
        data: state,
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        
        beforeSend: function() {
            $container.addClass('loading');
        },
        
        success: function(response) {
            $container.html(response.html);
        },
        
        error: function(xhr) {
            if (xhr.statusText !== 'abort') {
                console.error('[ModuleName] Request failed:', xhr);
            }
        },
        
        complete: function() {
            $container.removeClass('loading');
            currentRequest = null;
        }
    });
}
```

---

# 🏷️ NAMING CONVENTIONS

| Item        | Format         | Example             |
| ----------- | -------------- | ------------------- |
| File        | kebab-case     | `search-filter.js`  |
| Module      | PascalCase     | `SearchFilter`      |
| Init export | initModuleName | `initSearchFilter`  |
| Selectors   | UPPER_CASE     | `SELECTORS.trigger` |
| Functions   | camelCase      | `handleClick`       |
| jQuery vars | `$prefix`      | `$container`        |

---

# 🧩 CHECKLIST (AGENT MUST FOLLOW)

Before completing any task:

* [ ] Module does not already exist
* [ ] Uses IIFE pattern
* [ ] Uses ONLY `data-*` selectors
* [ ] Uses jQuery (no native DOM APIs)
* [ ] Uses scoped event delegation
* [ ] Events are namespaced
* [ ] DOM is cached
* [ ] State updated via `setState()`
* [ ] `init()` includes silent exit
* [ ] `destroy()` implemented
* [ ] Module registered in `modules-init.js`
* [ ] Console logs are prefixed

---

# 🏁 SUMMARY

This system guarantees:

* Predictable structure
* No event duplication bugs
* Safe dynamic DOM handling
* Scalable modular architecture
* Clean separation of concerns

 