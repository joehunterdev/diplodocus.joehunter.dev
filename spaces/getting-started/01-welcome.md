# Welcome to Diplodocus

![Diplodocus hero](attachments/01a-hero.png)

**Diplodocus** is a markdown-first, zero-build documentation site. Drop a folder
of `.md` files into your project, point your browser at `index.php`, and you
have a clean, searchable spaces site — sidebar, TOC, dark mode, code
highlighting, and image galleries included.

No Node. No npm. No database. No build step. Just PHP and markdown files.

## The one-sentence pitch

> Rename a file on disk. Refresh the browser. The sidebar has changed.

That's the whole product.

## How it compares

| | **Diplodocus** | Mintlify | Docusaurus | Plain GitHub |
|---|---|---|---|---|
| Language | PHP | React/Node | React/Node | — |
| Build step | ❌ none | ✅ required | ✅ required | ❌ none |
| Database | ❌ none | ❌ none | ❌ none | ❌ none |
| Hosting | Any PHP host | SaaS / Vercel | Vercel / Netlify | github.com |
| Works offline in one folder | ✅ | ❌ | ❌ | ⚠️ partial |
| Renders on GitHub if Diplodocus is removed | ✅ | ❌ | ❌ | ✅ |
| Search | ✅ client-side | ✅ | ✅ (Algolia) | ❌ |
| Image gallery | ✅ | ⚠️ | ⚠️ | ❌ |
| Open source | ✅ MIT | ❌ SaaS | ✅ MIT | — |

## What you get out of the box

- **Automatic sidebar** from your folder structure
- **Multi-project** — host many spaces under one install
- **Table of contents** generated from `##` headings with scroll-spy
- **Syntax highlighting** via highlight.js (PHP, JS, Python, Go, Rust, + 180 more)
- **Attachment gallery** — images, PDFs, CSVs, JSON, all previewable
- **Dark mode** — flip a CSS variable, whole site repaints
- **Security scan** — catches API keys, passwords, secrets in your markdown
- **Markdown lint** — heading hierarchy, link validity, code block completeness

## You are here

This `getting-started/` folder you're reading **is** a Diplodocus project. Every
feature the engine supports is demonstrated in one of the 12 pages in the
sidebar. Click through them in order and you'll have seen the whole product.

> **Tip** — The sidebar on the left lists every page in this guide. The
> "On this page" panel on the right (desktop only) is the table of contents
> for the current page.

## Where next

- [Installation](02-installation.md) — get Diplodocus running locally in 60 seconds
- [Folder structure](03-folder-structure.md) — the one convention you need to learn
- [Writing pages](04-writing-pages.md) — the full markdown cheat sheet
