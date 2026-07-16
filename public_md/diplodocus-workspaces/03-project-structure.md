# Project Structure — Organizing Your Docs

Every Diplodocus space (folder in `public_md/`) follows one of two structures: **flat-numbered** or **feature-driven**. This page explains both and helps you choose.

## Flat-Numbered (Default)

Files are numbered: `01-`, `02-`, `03-`, etc. They render in order, one per page.

**Best for:**
- Sequential guides (setup → deployment)
- Tutorials
- Onboarding docs
- Projects with a clear narrative flow

**Example structure:**

```
public_md/somenewproject-docs/
├── 01-welcome.md              → /somenewproject-docs/welcome
├── 02-getting-started.md      → /somenewproject-docs/getting-started
├── 03-installation.md         → /somenewproject-docs/installation
├── 04-configuration.md        → /somenewproject-docs/configuration
├── 05-deployment.md           → /somenewproject-docs/deployment
└── attachments/
    ├── setup-screenshot.png
    └── config-template.json
```

**URL pattern:**
- `01-welcome.md` → `http://site.com/somenewproject-docs/welcome`
- `02-getting-started.md` → `http://site.com/somenewproject-docs/getting-started`
- The number prefix is stripped from the URL

**Sidebar:** Renders as a vertical list in order.

**TOC (Table of Contents):** Extracted from `# H1` and `## H2` headings.

## Feature-Driven

Files organize by feature/concept folders. Each folder has its own `index.md` and related pages.

**Best for:**
- Large projects with multiple features
- Modular systems (auth, payments, reporting, etc.)
- Reference documentation
- Systems with parallel, independent topics

**Example structure:**

```
public_md/laravel-full-stack-like-a-pro/
├── auth/
│   ├── 01-setup.md            → /laravel-full-stack.../auth/setup
│   ├── 02-permissions.md      → /laravel-full-stack.../auth/permissions
│   └── 03-multi-tenant.md     → /laravel-full-stack.../auth/multi-tenant
├── database/
│   ├── 01-migrations.md       → /laravel-full-stack.../database/migrations
│   ├── 02-relations.md        → /laravel-full-stack.../database/relations
│   └── 03-query-optimization.md
├── api/
│   ├── 01-endpoints.md
│   ├── 02-authentication.md
│   └── 03-rate-limiting.md
└── attachments/
    └── db-schema.png
```

**URL pattern:**
- `auth/01-setup.md` → `http://site.com/laravel-full-stack.../auth/setup`
- The number prefix AND the feature folder are in the URL structure

**Sidebar:** Renders as collapsible feature groups.

**TOC:** Same as flat-numbered.

## How Diplodocus Knows Which One

By default, **Diplodocus assumes flat-numbered**. To use feature-driven, create a `.diplodocus.json` file in your space's root:

```json
{
  "spec": "feature-driven"
}
```

No `.diplodocus.json` = flat-numbered (safe default).

## Choosing Your Structure

### Use Flat-Numbered if:

- Your docs are a tutorial or sequential guide
- You have < 15 pages
- Users will read start-to-finish
- Examples: "Getting Started," "Setup Guide," "Deployment Steps"

### Use Feature-Driven if:

- Your docs cover multiple independent topics
- You have > 20 pages
- Users will jump around (reference docs)
- You have logical groupings (features, modules, systems)
- Examples: "API Reference," "Architecture Guide," "Feature Documentation"

## Creating Your First Project Docs Folder

Let's create `public_md/my-laravel-app-docs/` using flat-numbered structure:

```bash
cd ~/Projects/diplodocus-workspace/public_md

mkdir my-laravel-app-docs
cd my-laravel-app-docs

# Create the first page
cat > 01-overview.md << 'EOF'
# Overview

This is the documentation for My Laravel App.

## What is this?

A custom application for managing [your use case].

## Key Features

- Feature A
- Feature B
- Feature C
EOF

# Create an attachments folder for images
mkdir attachments
```

Your folder now looks like:

```
public_md/my-laravel-app-docs/
├── 01-overview.md
└── attachments/
```

## Naming Conventions

- **Folder slugs** use lowercase with hyphens: `my-laravel-app-docs`, `project-name-docs`
- **Filenames** use lowercase with hyphens: `01-getting-started.md`, `02-database-schema.md`
- **Attachment folder** is always named `attachments/` (Diplodocus scans for it)

## What's Next

Learn how to configure your space with `.diplodocus.json` and add more pages.
