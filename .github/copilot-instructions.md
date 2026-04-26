# GitHub Copilot Instructions — Diplodocus

## Project overview
Diplodocus is a **PHP 7.4-compatible**, markdown-first documentation site.
No Node, no npm, no build step. Drop `.md` files into `public/`, point a browser at `index.php`.

---

## Stack
| Layer | Technology |
|---|---|
| Language | PHP 7.4+ (no PHP 8-only functions — see below) |
| Templates | Plain PHP partials (`templates/`) |
| Styles | CSS custom properties via `assets/css/theme.css` + `diplodocus.css` |
| JS | Vanilla JS / jQuery modules in `assets/js/` — see `.github/javascript-instructions.md` |
| Routing | Clean paths (`/{space}/{page}`) via `.htaccess` → `index.php` |
| Markdown | Custom `lib/Parsedown.php` |

---

## Architecture
```
index.php               Bootstrap — instantiates App, calls run()
src/
  App.php               Request lifecycle, error handling
  Router.php            Parses clean URLs + legacy ?project= query params
  Config.php            Singleton config, merges config.php over defaults
  ProjectManager.php    Scans public/ for project folders
  ContentRenderer.php   Markdown → HTML via Parsedown
  TemplateEngine.php    PHP template renderer (layout + partials)
  Validator.php         Security scanner + markdown linter
lib/
  Parsedown.php         Custom markdown parser
  SecurityScanner.php   Detects secrets in .md files
  MarkdownLinter.php    Lint rules for markdown files
templates/
  layout.php            Outer HTML shell
  content.php           Three views: home dashboard / space landing / page
  partials/
    sidebar.php         Context-aware: home = all spaces, space = pages only
    header.php          Breadcrumb + toggle
    toc.php             Table of contents (page view only)
    modals.php          Validation results modal
public/                 User content — one subfolder per space
assets/                 CSS, JS, images (served directly by .htaccess)
config.php              Local overrides (gitignored)
config.example.php      Committed template for config.php
```

---

## Routing
| URL | Result |
|---|---|
| `/` | Home dashboard — all spaces as cards |
| `/{space}/` | Redirects to `/{space}/{first-page}` |
| `/{space}/{page}` | Renders that page |
| `/?validate=1` | Runs linter + security scanner |
| `/?action=download-project&project=x` | Downloads space as zip |

`Router::url()` always generates clean paths. Never hardcode `?project=` hrefs in templates — always use `$router->url([...])`.

---

## PHP compatibility rules
**Target: PHP 7.4.** Do not use:
- `str_ends_with()` / `str_starts_with()` / `str_contains()` → use `substr()` / `strpos()`
- `match` expressions (PHP 8.0)
- Named arguments (PHP 8.0)
- Enums (PHP 8.1)
- `readonly` properties (PHP 8.1)
- Nullsafe operator `?->` (PHP 8.0)
- `array_is_list()` (PHP 8.1)

---

## CSS conventions
- All design tokens live in `assets/css/theme.css` as `--dc-*` CSS variables
- `diplodocus.css` uses only `var(--dc-*)` — no hardcoded hex or px magic numbers
- Key layout variables: `--dc-sidebar-width`, `--dc-toc-width`, `--dc-content-max`
- Asset paths in templates must be **root-relative** (`/assets/css/...`), never relative, so clean URLs don't break them

---

## Template conventions
- All output must be escaped via `T::e()` (`Diplodocus\TemplateEngine::e()`)
- Templates receive `$router` as a global — use `$router->url([...])` for all internal links
- Three content views: `$content` set = page view; `$currentProject` + no `$content` = space landing; neither = home dashboard
- The `$engine` variable is always available inside templates for rendering partials

---

## Error handling
Controlled by two `config.php` keys:
- `'debug' => true` — shows full exception + stack trace (never in production)
- `'error_log' => '/path/to/error.log'` — always logs to file if set

---

## Workflow
See `.github/worfklow-instructions.md` for the feature brief → plan → implementation process.
Feature docs live in `.docs/{feature-name}/`.

---

## What NOT to do
- Don't add logic to `assets/js/app.js` directly — use modules (see JS instructions)
- Don't commit `config.php` or `config.local.php` (gitignored)
- Don't reference `public/` paths directly in PHP — always go through `ProjectManager`
- Don't add new PHP files without including them in `App.php` or the relevant class
- Don't use absolute file paths in CSS/JS `<link>`/`<script>` tags in templates
