# Claude Code Instructions

## Workflow

Full workflow spec: `.github/worfklow-instructions.md`

Before implementing any new feature:

1. Create a folder at `.docs/{feature-name}/`
2. Create `.docs/{feature-name}/{feature-name}-brief.md` — basic requirements and concerns 
3. Create `.docs/{feature-name}/{feature-name}-plan.md` — decisions, questions, sources, iterations (always prompt user here before going further)
4. Create `.docs/{feature-name}/{feature-name}-implementation.md` — implementation notes

Do not begin coding until the brief and plan files exist and are reviewed.

## JavaScript

Before writing any JavaScript, read `.github/javascript-instructions.md` in full.

Key rules:
- Always use the Revealing Module Pattern (IIFE)
- Never add inline logic to `assets/js/app.js` — use modules in `assets/js/modules/`
- Always check if a module already exists before creating one
- Register new modules by importing in `app.js` and calling `.init()` — **no `modules-init.js`, no `PageLogic`**
- End every module with `export default ModuleName;`
- Never use IDs or classes as JS hooks — use `data-*` attributes only
- Always use jQuery (`window.jQuery`)
- All events must be namespaced
- All modules must implement `destroy()`
- State must only be updated via `setState()`
