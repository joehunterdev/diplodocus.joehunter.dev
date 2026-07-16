# Welcome to Diplodocus Workspaces

Diplodocus is a **markdown-first documentation system** designed for developers who want to:

- Write docs alongside code (in Git, version-controlled)
- Keep docs close to projects without duplicating them
- Deploy docs instantly with zero build process
- Share guides across multiple projects and teams

This guide teaches the **workspace pattern** — the recommended way to use Diplodocus across multiple projects and repositories.

## The Problem This Solves

Typically, developers either:
- **Store docs in the project repo** — coupled to releases, hard to share across apps
- **Use a separate wiki or CMS** — disconnected from code, stale, duplicate effort
- **Write docs nowhere** — because the workflow is too heavy

Diplodocus Workspaces let you:

```
Your local machine:
  somenewproject/          ← Your actual app (Laravel, React, etc.)
  diplodocus-workspace/    ← Cloned docs repo (sparse, on-demand)

The deployed site:
  diplodocus.joehunter.dev/laravel-13-guide/     ← Shared guide
  diplodocus.joehunter.dev/somenewproject-docs/  ← Your project docs
```

Both live in the same Git repository. Locally, you clone only what you need. When you push, everything deploys.

## Key Ideas

**Sparse Checkout** — Clone only the folders you care about, not the entire repository.

```bash
git clone --filter=blob:none https://github.com/.../diplodocus.git
git sparse-checkout set public_md/laravel-13-guide public_md/somenewproject-docs
```

**Mounted Workspaces** — Diplodocus scans any folder in `public_md/` and renders it as a documentation space. No code required.

**Flat-Numbered Structure** — Files like `01-welcome.md`, `02-setup.md`, `03-deployment.md` auto-sort and render in order.

**Git as the Source** — Push to GitHub, Diplodocus picks it up. No admin panels, no database.

## What You'll Learn

1. **Local Setup** — Clone and sparse-checkout the docs repo
2. **Project Structure** — How to organize your docs folder
3. **Configuration** — The optional `.diplodocus.json` file
4. **Adding Custom Docs** — Create a folder for your project
5. **Deployment** — Push to GitHub and watch it go live
6. **Real Example** — Complete walkthrough from zero to deployed

Ready? Let's start.
