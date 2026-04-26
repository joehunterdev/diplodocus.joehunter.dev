# Folder Structure

Diplodocus has exactly one convention. Learn this page and you know the whole
content model.

![Folder tree](attachments/03a-folder-tree.png)

## The convention

```
diplodocus-root/
├── index.php              ← Diplodocus engine (don't touch)
├── src/                   ← Engine internals (don't touch)
├── lib/                   ← Vendored libraries (don't touch)
├── assets/                ← CSS + JS (edit theme.css to rebrand)
├── templates/             ← HTML shell (don't touch unless you know why)
│
├── getting-started/       ← ← YOUR public project (this one!)
│   ├── 01-welcome.md
│   ├── 02-installation.md
│   ├── 03-folder-structure.md
│   └── attachments/
│       ├── 01a-hero.png
│       └── 03a-folder-tree.png
│
└── my-other-project/         ← ← Another project
    ├── 01-overview.md
    ├── 02-reference.md
    └── attachments/
```

## Rule 1 — each top-level folder is a project

Any folder at the project root that isn't in the engine's reserved list
(`src`, `lib`, `assets`, `templates`, `.backup`, etc.) becomes a
**project** in the sidebar.

Add a folder, get a project. Delete the folder, the project is gone.

## Rule 2 — files in a project are pages

Any `.md` file inside a project folder becomes a **page**. The filename
controls both the order and the sidebar label.

```
getting-started/
├── 01-welcome.md              ← page 1, label: "Welcome"
├── 02-installation.md         ← page 2, label: "Installation"
└── 03-folder-structure.md     ← page 3, label: "Folder Structure"
```

### The filename pattern

```
NN-slug-with-hyphens.md
│    │
│    └── slug → label (hyphens become spaces, title-cased)
└── NN → numeric ordering (01, 02, … 99)
```

| Filename | Sidebar label | Sort position |
|---|---|---|
| `01-welcome.md` | Welcome | 1 |
| `02-installation.md` | Installation | 2 |
| `10-theming-and-branding.md` | Theming & Branding | 10 |
| `99-appendix.md` | Appendix | 99 |

## Rule 3 — `attachments/` sits beside the pages

Every project can have its own `attachments/` subfolder for images, PDFs,
CSVs, JSON, and other binary content. Reference them with relative paths.

```markdown
![Architecture diagram](attachments/02a-architecture.png)
[Download the spec](attachments/05b-spec.pdf)
```

### Attachment naming convention

Prefix each attachment with the page number it belongs to. This keeps
attachments grouped with the page that uses them and makes pruning easy.

```
attachments/
├── 01a-hero.png              ← used by 01-welcome.md
├── 03a-folder-tree.png       ← used by 03-folder-structure.md (first)
├── 03b-sidebar-example.png   ← used by 03-folder-structure.md (second)
└── 07a-highlight-theme.png   ← used by 07-code-blocks.md
```

## Rule 4 — multi-project is free

Want to host multiple spaces sites under one Diplodocus install? Just create
more top-level folders.

```
diplodocus-root/
├── api-docs/           ← "API Docs" project
├── user-guide/         ← "User Guide" project
├── internal-wiki/      ← "Internal Wiki" project
└── getting-started/    ← this project
```

Each project is independent — its own pages, its own attachments, its own
sidebar order. Users switch between them from the top-level project picker.

## What the engine *doesn't* care about

- No `index.md` required — the first numbered page is landed on by default
- No frontmatter — filenames do the work
- No nested folders for pages — one flat folder per project, keeps things simple
- No build step — filesystem **is** the source of truth

## Next

- [Writing pages](04-writing-pages.md) — markdown primitives
- [Attachments & images](05-attachments-and-images.md) — using your `attachments/` folder
