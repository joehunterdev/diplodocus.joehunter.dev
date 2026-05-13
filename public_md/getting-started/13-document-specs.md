# Document Specs

A **spec** is the folder layout a project uses. Diplodocus ships with two:

| Spec | Use it when… |
|---|---|
| `flat-numbered` (default) | You're writing a manual, guide, or reference — pages have a natural reading order. |
| `feature-driven` | You're building software and each feature lives as brief → plan → implementation. |

A project picks its spec with a one-line marker file. No engine code
changes, no build step, no schema. Add the file, the sidebar reshapes.

## How a project declares its spec

Drop a `.diplodocus.json` at the project root:

```json
{
  "spec": "feature-driven"
}
```

If the file is missing, the project defaults to `flat-numbered` — the
shape you learned in [Folder structure](03-folder-structure.md). That's
why this `getting-started/` project, the one you're reading right now,
just works: no marker file, default spec.

## Spec 1 — `flat-numbered`

The default. Pages live as `NN-slug.md` files at the project root, with
an `attachments/` sibling for images and downloads.

```
my-guide/
├── 01-intro.md
├── 02-setup.md
├── 03-usage.md
└── attachments/
    ├── 01a-hero.png
    └── 02a-diagram.svg
```

- **Order:** numeric prefix (`NN-`) controls sidebar position
- **Labels:** the slug after the prefix becomes the sidebar label
- **Attachments:** one shared `attachments/` folder, prefixed by page number
- **Sidebar:** flat list, one entry per page

Full details in [Folder structure](03-folder-structure.md).

## Spec 2 — `feature-driven`

Each feature lives in its own subfolder containing up to three docs:
a **brief**, a **plan**, and an **implementation**. The three docs
together describe one feature end-to-end.

```
my-features/
├── .diplodocus.json     ← { "spec": "feature-driven" }
├── auth-flow/
│   ├── auth-flow-brief.md
│   ├── auth-flow-plan.md
│   ├── auth-flow-implementation.md
│   ├── a-flow-diagram.png
│   └── b-state-machine.svg
└── billing/
    ├── billing-brief.md
    ├── billing-plan.md
    └── billing-implementation.md
```

### The 3-doc rhythm

| Doc | Purpose |
|---|---|
| **brief** | What this feature is and why it exists, in plain language. No implementation details. |
| **plan** | How you're going to build it. Decisions, trade-offs, phasing. Locks in approach before code. |
| **implementation** | What you actually built. Files touched, verification, design rationale. Written *as* you go, not after. |

The naming is strict: `{feature-folder-name}-brief.md`,
`{feature-folder-name}-plan.md`,
`{feature-folder-name}-implementation.md`. The folder name and the file
prefix must match — that's how the engine knows the three files belong
to the same feature.

### Rules

- **Sidebar:** each feature becomes a labeled group with Brief / Plan / Implementation as 3 child links.
- **Group click → brief.** The feature name in the sidebar is itself a link; clicking it opens that feature's brief.
- **Partial features are allowed.** You can start a feature with just a brief. Add the plan and implementation files as you go; the sidebar picks them up automatically. Missing docs are silently skipped, not errors.
- **Alphabetical order.** Features sort A→Z by folder name. Within a feature, docs always read brief → plan → implementation.
- **No `attachments/` folder.** Assets sit flat inside the feature folder, prefixed `a-`, `b-`, … The feature folder *is* the namespace; there's nothing to separate from.

### URLs

Pages use a flat slug: the URL is `/{project}/{feature}-{doc-type}`,
**not** nested. So:

| File | URL |
|---|---|
| `my-features/auth-flow/auth-flow-brief.md` | `/my-features/auth-flow-brief` |
| `my-features/auth-flow/auth-flow-plan.md` | `/my-features/auth-flow-plan` |
| `my-features/billing/billing-implementation.md` | `/my-features/billing-implementation` |

The slug carries the feature name and doc type together; the engine
recovers the folder from the slug, no nested routing needed.

## A live example

There's a working feature-driven project shipped with this install
to make the spec self-documenting. Look at the sidebar's project
switcher — it's the one named **Documentation Example Feature
Driven**. Inside you'll find:

- `some-new-feature/` — a fully-populated feature (brief + plan + implementation), showing the canonical rhythm
- `auth-flow/` — a feature with **only a brief**, demonstrating the partial-features rule. The sidebar shows just the brief; the plan and implementation links don't appear until you create those files

Copy that folder, rename, and start writing.

## When to use which

A rough decision tree:

- **Writing docs others will read in order?** → `flat-numbered`. Manuals, guides, references, runbooks — anything with a natural narrative.
- **Building software, capturing decisions as you go?** → `feature-driven`. The brief/plan/implementation rhythm is well-suited to AI-assisted development where briefs feed plans feed implementations.
- **Both?** Run two projects side-by-side in the same install. One folder per project, each picks its own spec.

> **Tip** — The two specs can coexist. Your `getting-started/` manual
> (flat-numbered) and a `features/` project (feature-driven) live happily
> in the same Diplodocus install — each picks the spec that fits its content.

## Adding a third spec

The spec system is pluggable. Two ship in the box; nothing stops you
from adding a third — an ADR log, a Mintlify-style mintlify.json
reader, a date-prefixed changelog format — by writing a class that
implements `Diplodocus\Spec\SpecHandler`.

The interface has three methods:

```php
interface SpecHandler {
    public function getName(): string;
    public function getPages(string $projectPath): array;
    public function getAssetBase(string $projectPath, string $pageSlug): string;
    public function getSidebarTree(string $projectPath): array;
}
```

| Method | Returns |
|---|---|
| `getName()` | The string you put in `.diplodocus.json`'s `spec` field |
| `getPages()` | Flat list of pages: `[order, slug, name, path]` per page |
| `getAssetBase()` | Where bare image filenames resolve, relative to the project root (e.g., `attachments/` or `{feature}/`) |
| `getSidebarTree()` | The sidebar shape — either flat `type: 'page'` entries or nested `type: 'group'` entries with `children` |

Register your spec in `src/ProjectManager.php`'s `buildSpec()` switch,
and any project that drops `{"spec": "your-spec-name"}` into its marker
file will start using it.

## Next

- [Folder structure](03-folder-structure.md) — full reference for the default `flat-numbered` spec
- [Deploying](11-deploying.md) — pushing your spaces to a host
