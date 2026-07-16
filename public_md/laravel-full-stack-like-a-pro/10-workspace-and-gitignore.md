# Workspace & Gitignore

Two files that shape the day-to-day feel of a project: the VS Code **`.code-workspace`** (what the editor hides, watches, and searches) and the **`.gitignore`** (what never enters history). Both are copied in at project start and tweaked, not rewritten.

## `my-app.code-workspace`

Open the workspace file, not the folder — it carries the exclude lists that keep search fast and the file tree signal-only.

```json
{
    "folders": [
        {
            "path": "."
        }
    ],
    "settings": {
        "todo-tree.tree.showBadges": true,
        "todo-tree.tree.scanMode": "workspace only",
        "files.exclude": {
            "**/node_modules": true,
            "**/vendor": true,
            "**/.git": true,
            "**/.DS_Store": true,
            "**/Thumbs.db": true,
            "**/package-lock.json": true,
            "**/yarn.lock": true,
            "**/.phpunit.result.cache": true,
            "**/.phpunit.cache": true,
            "**/storage/framework/cache/**": true,
            "**/storage/framework/sessions/**": true,
            "**/storage/framework/views/**": true,
            "**/storage/debugbar/**": true,
            "**/public/hot": true,
            "**/public/storage": true
        },
        "search.exclude": {
            "**/node_modules": true,
            "**/vendor": true,
            "**/storage/framework": true,
            "**/public/css": true,
            "**/public/js": true,
            "**/public/fonts": true,
            "**/public/webfonts": true,
            "**/public/build": true,
            "**/.phpunit.cache": true,
            "**/composer.lock": true,
            "**/package-lock.json": true,
            "**/*.min.css": true,
            "**/*.min.js": true,
            "**/*.map": true,
            "**/dist": true,
            "**/.private": true,
            "**/.private/**": true,
            "**/*.bak": true
        },
        "files.watcherExclude": {
            "**/node_modules/**": true,
            "**/vendor/**": true,
            "**/storage/framework/**": true,
            "**/public/css/**": true,
            "**/public/js/**": true,
            "**/public/fonts/**": true,
            "**/public/webfonts/**": true
        }
    }
}
```

| Block | Why it matters |
|---|---|
| `files.exclude` | Hides installed/generated noise from the explorer tree |
| `search.exclude` | Keeps `Ctrl+Shift+F` results to *your* code — no vendor, no lockfiles, no build output, no `.private` |
| `files.watcherExclude` | Stops the FS watcher burning CPU on `vendor/` and `storage/framework/` churn |
| todo-tree settings | Project-scoped TODO scanning with badges |

> **Tip** — The `**/.private` search exclusion pairs with the gitignore's `**/.private/` rule below: a `.private/` folder anywhere in the repo is invisible to git *and* to editor search. That's where local credentials CSVs, scratch notes, and client-sensitive docs live.

## `.gitignore`

The full base — Laravel defaults plus the deploy-aware and privacy rules layered on:

```gitignore
/.phpunit.cache
/node_modules
/.vite
/playwright-report
/test-results
/blob-report
/playwright/.cache
/storage/*.key
/storage/logs/**
/storage/framework/sessions/**
/storage/framework/cache/**
/storage/framework/views/**
/bootstrap/cache/
/vendor
*.env
.phpunit.result.cache
npm-debug.log
yarn-error.log
/.idea
/.vscode
/.fleet

# Hide .private folders anywhere
**/.private/
.env.local
.env.development
.env.production
.env.staging

# Testing
/tests
phpunit.xml

# Development scripts
/scripts

# Copilot/MCP/Boost config (local dev only)
# .github/copilot-instructions.md - TRACKED: contains project-wide coding standards
mcp.json
boost.json
TODO.md

# Keep essential public files
!/public/index.php
!/public/robots.txt
!/public/.htaccess

# Vite production assets (commit for deployment without rebuilding on server)
!/public/build/
!/public/build/**

# Exclude source maps and helper chunks (dev-only)
/public/build/js/*.map
/public/build/css/*.map

# Debug build output (unminified, never commit)
/public/build-debug/

# Vite dev server HMR indicator (never commit)
/public/hot

# User-uploaded content (via storage symlink) — keep defaults, exclude uploads
/public/storage/**
/storage/app/public/uploads/**

# Docs are local-only by default; whitelist what should ship
.docs/**
```

## The decisions encoded in it

| Rule | Decision |
|---|---|
| `*.env` + `.env.staging/.production` | **No env file ever enters history** — including the per-environment copies `remote:cmds` reads locally |
| `**/.private/` | A private folder works anywhere in the tree — credentials CSVs, client docs, scratch notes |
| `!/public/build/**` | **Commit the Vite build.** Shared hosting doesn't run Node; assets deploy pre-built from the repo — but keep `.map` files and `build-debug/` out |
| `/public/hot` | The Vite HMR marker must never deploy — a stale `hot` file makes production try to load assets from your dev server |
| `!/public/.htaccess` | The hardened htaccess ([page 09](09-htaccess-examples.md)) is part of the app — force-track it |
| `/tests` + `phpunit.xml` on release | Dev-only files pruned from release branches; the FTP `exclude` list ([page 08](08-deployment.md)) backs this up |
| `.docs/**` | Project docs are local by default — un-ignore individual files you *want* published |
| Config with private leanings | If a `config/deployment.php` ever exists, it gets ignored too — see [Environment & config](06-environment-and-config.md) |

> **Warning** — `boost.json` and `mcp.json` are ignored here as local-dev tooling, but `.github/copilot-*.md` instructions and `.github/skills/` **are tracked** — they're the shared team/AI standards, not personal config. Don't blanket-ignore `.github`.

## Next

- [Feature flow & docs](11-feature-flow-and-docs.md)
